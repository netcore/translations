<script>
    init.push(function () {

        if ($('#languages-index-datatable').length) {

            var dataTable = $('#languages-index-datatable').dataTable({
                responsive: true,

                "columns": [
                    { "orderable": true }, // Title
                    { "orderable": true }, // Title localized
                    { "orderable": true }, // ISO kods
                    { "orderable": true }, // Fallback
                    { "orderable": false }, // Actions
                ],

                order: [[ 0, 'desc' ]], // Default is to order by Title
            });

            $('#languages-index-datatable_wrapper .table-caption').text('{{ array_get($uiTranslations, 'languages') }}');
            $('#languages-index-datatable_wrapper .dataTables_filter input').attr('placeholder', '{{ array_get($uiTranslations, 'search') }}');
        }



        $('body').on('click', '.translations-in-database-confirm-action',function(e){

            console.log('specific package code');

            e.preventDefault();

            var btn = $(this);

            swal({
                title: $(btn).data('title'),
                text: $(btn).data('text'),
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: $(btn).data('confirm-button-text'),
                closeOnConfirm: true
            }, function(){

                $.ajax({
                    url: btn.attr('href'),
                    type: btn.data('method'),
                    dataType: 'json',

                    success: function (response) {

                        window.setTimeout(function () {
                            swal({
                                title: btn.data('success-title'),
                                text: btn.data('success-text'),
                                type: "success"
                            });
                        }, 100);

                        if (btn.data('id')) {
                            if ($('.object' + btn.data('id')).length) {
                                $('.object' + btn.data('id')).fadeOut();
                            }
                            else {
                                btn.closest('tr').fadeOut();
                            }
                        }

                    },
                    error: function (xhr) {
                        console.log(xhr);
                        window.setTimeout(function () {
                            swal("Error", xhr.responseText, "error");
                        }, 100);
                    }
                });

            });
        });
    });
</script>