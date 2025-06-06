$(function() {
    "use strict";

    $(document).ready(function () {

        var url = $('#url').val();

        categoryByAjax();
        sellerAjax();
        stateAjax();
        timezoneAjax();
        blogAjax();
        brandAjax();

        $('.all-user-by-ajax').select2({
            placeholder: "Select User",
            minimumInputLength: 2,
            ajax: {
                type: "GET",
                dataType: 'json',
                url: url + '/' + 'admin/all-user-by-ajax',
                data: function (params) {
                    return {
                        q: params.term // search term
                    };
                },
                delay: 250,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });

        $('.user-by-ajax').select2({
            placeholder: "Select User",
            minimumInputLength: 2,
            closeOnSelect: true,
            'multiple': true,
            ajax: {
                type: "GET",
                dataType: 'json',
                url: url + '/' + 'admin/user-by-ajax',
                data: function (params) {
                    return {
                        q: params.term // search term
                    };
                },
                delay: 250,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });
    });

});

function categoryByAjax(section_no) {
    var url = $('#url').val();
    var user_type = $('#get_user_type').val();

    let selector;

    if (section_no)
    {
        selector = $('.filter-categories-by-ajax_'+section_no);
    }
    else{
        selector = $('.filter-categories-by-ajax');
    }
    let date = new Date();
    let timestamp = date.getMilliseconds();
    selector.select2({
            placeholder: "Select Category",
            minimumInputLength: 2,
            ajax: {
                type: "GET",
                dataType: 'json',
                url: url + '/' + user_type + '/categories-by-ajax?time='+timestamp,
                data: function (params) {
                    return {
                        q: params.term // search term
                    };
                },
                delay: 250,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });
}

function sellerAjax(section_no)
{
    var url = $('#url').val();

    let selector;

    if (section_no)
    {
        selector = $('.seller-by-ajax_'+section_no);
    }
    else{
        selector = $('.seller-by-ajax');
    }
    var user_type = $('#get_user_type').val();
    let date = new Date();
    let timestamp = date.getMilliseconds();
    selector.select2({
        placeholder: "Select Seller",
        minimumInputLength: 2,
        ajax: {
            type: "GET",
            dataType: 'json',
            url: url + '/' + user_type +'/seller-by-ajax?time='+timestamp,
            data: function (params) {
                return {
                    q: params.term // search term
                };
            },
            delay: 250,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
            cache: true
        }
    });
}

function blogAjax(section_no)
{
    var url = $('#url').val();
    var user_type = $('#get_user_type').val();

    let selector;

    if (section_no)
    {
        selector = $('.filter-blogs-by-ajax_'+section_no);
    }
    else{
        selector = $('.filter-blogs-by-ajax');
    }
    let date = new Date();
    let timestamp = date.getMilliseconds();
    selector.select2({
        placeholder: "Select Blog",
        minimumInputLength: 2,
        ajax: {
            type: "GET",
            dataType: 'json',
            url: url + '/' + user_type + '/blogs-by-ajax?time='+timestamp,
            data: function (params) {
                return {
                    q: params.term // search term
                };
            },
            delay: 250,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
            cache: true
        }
    });
}

function stateAjax(section_no)
{
    var url = $('#url').val();

    let selector;

    if (section_no)
    {
        selector = $('.state-by-ajax_'+section_no);
    }
    else{
        selector = $('.state-by-ajax');
    }
    var user_type = $('#get_user_type').val();
    let date = new Date();
    let timestamp = date.getMilliseconds();
    selector.select2({
        placeholder: "Select State",
        minimumInputLength: 2,
        ajax: {
            type: "GET",
            dataType: 'json',
            url: url + '/' +user_type+ '/state-by-ajax?time='+timestamp,
            data: function (params) {
                return {
                    q: params.term // search term
                };
            },
            delay: 250,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
            cache: true
        }
    });
}


function timezoneAjax(section_no)
{
    var url = $('#url').val();

    let selector;

    if (section_no)
    {
        selector = $('.timezone-by-ajax_'+section_no);
    }
    else{
        selector = $('.timezone-by-ajax');
    }
    var user_type = $('#get_user_type').val();

    selector.select2({
        placeholder: "Select Time Zone",
        minimumInputLength: 2,
        ajax: {
            type: "GET",
            dataType: 'json',
            url: url + '/' + user_type+ '/time-zone-by-ajax',
            data: function (params) {
                return {
                    q: params.term // search term
                };
            },
            delay: 250,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
            cache: true
        }
    });
}
function brandAjax(section_no)
{
    var url = $('#url').val();

    let selector;

    if (section_no)
    {
        selector = $('.brand-by-ajax_'+section_no);
    }
    else{
        selector = $('.brand-by-ajax');
    }
    var user_type = $('#get_user_type').val();

    selector.select2({
        placeholder: "Select Brand",
        minimumInputLength: 2,
        ajax: {
            type: "GET",
            dataType: 'json',
            url: url + '/' + user_type+ '/brands-by-ajax',
            data: function (params) {
                return {
                    q: params.term // search term
                };
            },
            delay: 250,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
            cache: true
        }
    });
}