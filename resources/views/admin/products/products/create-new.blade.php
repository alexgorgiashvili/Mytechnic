@extends('admin.partials.master')

@section('product-createHttp')
    active
@endsection
@section('product_active')
    active
@endsection

@php
    $title = '';
    if(isset($is_digital)):
        $title =  __('Add Digital Product');
    elseif(isset($is_catalog)):
        $title =  __('Add Catalog Product');
    elseif(isset($is_classified)):
       $title = __('Add Classified Product');
    else:
       $title = __('Add Product');
    endif;
@endphp
@section('title')
    {{ $title }}
@endsection

@section('main-content')
    <section class="section">
        <div class="section-body">
           

            <form action="{{ route('admin.product.sendHttp') }}" method="post" enctype="multipart/form-data"
                  data-form="{{ route('get-variants') }}" id="variant">
                @csrf
                <input class="form-control" type="text" name="url" placeholder="Type Product URL">
                <button type="submit"  class="btn btn-outline-primary mt-2">Save</button>
            </form>
        </div>
    </section>

    {{-- @include('admin.common.selector-modal') --}}
@endsection

@section('style')
    <link rel="stylesheet" href="{{ static_asset('admin/css/dropzone.css') }}">
@endsection
@section('page-style')
    <link rel="stylesheet" href="{{ static_asset('admin/css/summernote-bs4.css') }}">
    <link rel="stylesheet" href="{{ static_asset('admin/css/daterangepicker.css') }}">
@endsection
@push('page-script')
    <script src="{{ static_asset('admin/js/summernote-bs4.js') }}"></script>
    <script type="text/javascript" src="{{ static_asset('admin/js/daterangepicker.min.js') }}"></script>
    @if(addon_is_activated('ai_writer'))
        <script src="{{ static_asset('admin/js/ai_writer.js') }}"></script>
    @endif
@endpush
{{-- @push('script')
    <script type="text/javascript" src="{{ static_asset('admin/js/dropzone.min.js') }}"></script>
    <script type="text/javascript">
        $(function () {
            var toAdd = $('.daterange-cus');
            toAdd.daterangepicker({
                autoUpdateInput: false,
                timePicker: true,
                locale: {
                    cancelLabel: '{{ __('Clear') }}',
                    format: 'M-DD-YYYY hh:mm A'
                }
            });
            toAdd.on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('MM-DD-YYYY hh:mm A') + ' - ' + picker.endDate.format('MM-DD-YYYY hh:mm A'));
            });
            toAdd.on('cancel.daterangepicker', function () {
                $(this).val('');
            });
            $(document).ready(function () {
                $(document).on('change', '#description_images', function () {

                    let input = this;

                    if (input.files) {
                        $('#description_image_modal').empty();
                        var filesAmount = input.files.length;

                        for (let i = 0; i < filesAmount; i++) {
                            var reader = new FileReader();

                            reader.onload = function (event) {
                                $($.parseHTML('<img class="description_image">')).attr('src', event.target.result).appendTo('#description_image_modal');
                            }

                            reader.readAsDataURL(input.files[i]);
                        }
                    }
                })
            })

        });
    </script>
@endpush --}}
