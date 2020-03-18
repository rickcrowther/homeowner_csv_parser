@extends('layouts.blank')
@section('main_container')
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h1 class="display-3">
                    Homeowner CSV Parser
                </h1>
            </div>
        </div>
    </div>
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="p-4 border-bottom bg-dark">
                <h4 class="card-title mb-0 text-white">CSV Select</h4>
            </div>
            <div class="card-body">
                <h4 class="text-muted mb-3">Select the CSV you wish to parse then press the Go button</h4>
                <div class="row no-gutters">
                    <div class="col flex-column">
                        <div class="d-flex align-items-center">
                            <form class="form-horizontal file-upload" method="post" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <div class="form-group mb-2">
                                    <div class="input-group">
                                        <input type="file" name="homeowner_csv" id="homeowner_csv" class="dropify" data-allowed-file-extensions="csv" />
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-lg btn-success btn-submit mb-2">Go</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('includes/flash')

    <div class="col-md-12 grid-margin stretch-card">
        <div class="card" id="output_card" hidden="true">
            <div class="p-4 border-bottom bg-info">
                <h4 class="card-title mb-0 text-white">Here's your output:</h4>
            </div>
            <div class="circle-loader" id="output_card_loader" hidden="true"></div>
            <div class="card-body" id="output_card_body" hidden="true">
                @include('partials.csv_output')
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script type="text/javascript">

        $(function(){
            $('.dropify').dropify();

            $('form').submit(function(event){
                event.preventDefault();
                var formData = new FormData($(this)[0]);
                if(!$("#homeowner_csv").val()){
                    return flash_error('You must select a File before pressing Go');
                }
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': '{{csrf_token()}}'
                    },
                    url: '{{ route("parse_csv") }}',
                    data: formData,
                    type: 'post',
                    processData: false,
                    contentType: false,
                }).done(function(data){
                    $('div#flash').prop("hidden", true);
                    $('div#output_card').prop("hidden", false);
                    $('div#output_card_body').prop("hidden", true);
                    $('div#output_loader').prop("hidden", false);
                    $('div#output_card_body').html(data);
                    $('div#output_loader').prop("hidden", true);
                    $('div#output_card_body').prop("hidden", false);
                }).fail(function(e) {
                    console.log('error', e);
                    $('div#output_card').prop("hidden", true);
                    flash_error(JSON.parse(e.responseText).message);
                });

            });

        });

        function flash_error(error_message){
            $('div#flash span#error_message').html('There was a problem with your request.' + error_message);
            $('div#flash').prop("hidden", false);
            setTimeout(function() {
                $('div#flash').prop("hidden", true);
            }, 5000);
        }

    </script>

@endpush
