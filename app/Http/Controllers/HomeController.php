<?php

namespace App\Http\Controllers;

use App\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class HomeController extends Controller
{

    private $text_ext = [ 'doc', 'docx', 'xls', 'xlsx', 'pdf'];
    private $pic_ext = ['jpg', 'png', ];
    private $archive_ext = ['rar', 'zip'];
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        return view('files');
    }

    public function show($id)
    {
        redirect('files');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return  File::where('id',$id)->where('user_id',Auth::user()->id)->firstOrFail();
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|alpha_dash|max:40',
            'file' => 'required|file|mimes:doc,docx,xls,xlsx,pdf,jpeg,png,rar,zip|max:200000'
        ]);

        if ($validator->fails())
        {

            //Log::debug($validator->errors()->all());
            return response()->json([
                'error' => true,
                'message' => 'Wrong File Type or Name'
            ]);
        }

        $newfile  = new File();

        $file = $request->file('file');
        $title = count($request['title']) > 0 ? $request['title'] : $this->translit(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $ext = $file->getClientOriginalExtension();

        $type = $this->getType($ext);

        $newname = sha1($title . time()) . '.' . $ext;

        if (Storage::putFileAs('/files/' . Auth::id() . '/', $file, $newname)) {
            return $newfile::create([
                'user_id' => Auth::id(),
                'title' => $title,
                'filename' => $newname,
                'type' => $type,
                'size' => $this->getHumanSize($file->getClientSize()),
                'description' => count($request['description']) > 0 ? $request['description'] : "",
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'File Created'
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $file = File::where('id',$id)->where('user_id',Auth::user()->id)->firstOrFail();
        $file->update($request->all());
        return response()->json([
            'success' => true,
            'message' => 'File Updated'
        ]);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $file = File::findOrFail($id);

        if ($file->user_id != Auth::id()){
            return response()->json([
                'error' => true,
                'message' => 'You don\'t have premission',
            ]);
        }
        File::destroy($id);
        Storage::delete('/files/' . Auth::id() . '/'.$file->filename);
        return response()->json([
            'success' => true,
            'message' => 'File Deleted'
        ]);
    }


    /**
     * Create Result Table from files.
     *
     * @return \Illuminate\Http\Response
     */
    public function apiFiles()
    {
        $file = File::where('user_id',Auth::id())->get();
        $start = 0;

        return Datatables::of($file)
            ->addColumn('action', function($file){
                return '<a href="'.url('/').'/download/'.$file->id.'" onclick="reloadTable()" class="btn btn-success btn-xs"><i class="glyphicon glyphicon-download-alt"></i> Download</a> ' .
                    '<a onclick="editForm('. $file->id .')" class="btn btn-primary btn-xs"><i class="glyphicon glyphicon-edit"></i> Edit</a> ' .
                    '<a onclick="deleteFile('. $file->id .')" class="btn btn-danger btn-xs"><i class="glyphicon glyphicon-trash"></i> Delete</a>';
            })
            ->addColumn('smalldescription',function ($file){
                return '<a href="#" onclick="getFullDescription('.$file->id.'); return false">'.$file->smalldescription().'</a>';
            })
            ->addColumn('type_file', function ($file){ return $file->getType();  })
            ->addIndexColumn()
            ->removeColumn('type')
            ->removeColumn('description')
            ->rawColumns(['action','smalldescription','type_file'])->make(true);
    }


    /**
     * Download file
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function download($id)
    {
        $file = File::where('id',$id)->where('user_id',Auth::id())->firstOrFail();

        if(Storage::exists('/files/' . Auth::id() . '/'.$file->filename)) {
            $ext = pathinfo(storage_path().'/files/' . Auth::id() . '/'.$file->filename, PATHINFO_EXTENSION);
            $down = (int)$file->downloaded;
            $file->downloaded = $down+1;
            $file->save();
            return Storage::download('/files/' . Auth::id() . '/' . $file->filename, $file->title.'.'.$ext);
        }
        else { return redirect('file');}

    }
    /**
     * Show full description from file
     *
     * @return \Illuminate\Http\Response
     */
    public function fullDescription($file)
    {
        $desc = File::where('id',$file)->where('user_id',Auth::id())->firstOrFail()->fulldescription();

        return $desc;

    }

    /**
     * Get type by extension
     * @param  string $ext Specific extension
     * @return int      Type
     */
    private function getType($ext)
    {

        if (in_array($ext, $this->archive_ext)) {
            return 3;
        }

        if (in_array($ext, $this->pic_ext)) {
            return 2;
        }

        if (in_array($ext, $this->text_ext)) {
            return 1;
        }



    }

    /**
     * Format bytes to kb, mb, gb, tb
     *
     * @param  integer $size
     * @param  integer $precision
     * @return integer
     */
    private function getHumanSize($size, $precision = 2)
    {
        if ($size > 0) {
            $size = (int) $size;
            $base = log($size) / log(1024);
            $suffixes = array(' bytes', ' KB', ' MB', ' GB', ' TB');

            return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
        } else {
            return $size;
        }
    }

    /**
     * Translite Cyrylic filenames
     *
     * @param  string $filename
     * @return string
     */
    private function translit($str){
        $alphavit = array(
            /*--*/
            "а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d","е"=>"e",
            "ё"=>"yo","ж"=>"j","з"=>"z","и"=>"i","й"=>"i","к"=>"k","л"=>"l", "м"=>"m",
            "н"=>"n","о"=>"o","п"=>"p","р"=>"r","с"=>"s","т"=>"t",
            "у"=>"y","ф"=>"f","х"=>"h","ц"=>"c","ч"=>"ch", "ш"=>"sh","щ"=>"sh",
            "ы"=>"i","э"=>"e","ю"=>"u","я"=>"ya",
            /*--*/
            "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G","Д"=>"D","Е"=>"E", "Ё"=>"Yo",
            "Ж"=>"J","З"=>"Z","И"=>"I","Й"=>"I","К"=>"K", "Л"=>"L","М"=>"M",
            "Н"=>"N","О"=>"O","П"=>"P", "Р"=>"R","С"=>"S","Т"=>"T","У"=>"Y",
            "Ф"=>"F", "Х"=>"H","Ц"=>"C","Ч"=>"Ch","Ш"=>"Sh","Щ"=>"Sh",
            "Ы"=>"I","Э"=>"E","Ю"=>"U","Я"=>"Ya",
            "ь"=>"","Ь"=>"","ъ"=>"","Ъ"=>""
        );
        return strtr($str, $alphavit);
    }
}
