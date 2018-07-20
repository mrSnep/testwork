<div class="modal" id="modal-form" tabindex="1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="form-contact" method="post" class="form-horizontal" data-toggle="validator" enctype="multipart/form-data">
                {{ csrf_field() }} {{ method_field('POST') }}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"> &times; </span>
                    </button>
                    <h3 class="modal-title"></h3>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    <div class="form-group">
                        <label for="title" class="col-md-3 control-label">Title</label>
                        <div class="col-md-6">
                            <input type="text" pattern="[_A-z0-9-.]{1,40}$" id="title" name="title" class="form-control" autofocus>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-12">
                        <label for="description" class="control-label">Description</label>

                            <textarea class="form-control" id="description" name="description" rows="3" ></textarea>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>


                    <div class="form-group foto_group">
                        <label for="file" class="col-md-3 control-label">File</label>
                        <div class="col-md-6">
                            <input type="file" id="file" name="file" class="form-control">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-save">Submit</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>

            </form>
        </div>
    </div>
</div>
