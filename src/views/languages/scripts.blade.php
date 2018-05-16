<script>
	init.push(function () {
		var dataTable = $('#languages-index-datatable');

		if (dataTable.length) {
			dataTable.dataTable({
				responsive: true,
				order: [[0, 'desc']], // Default is to order by Title
				columns: [
					{'orderable': true}, // Title
					{'orderable': true}, // Title localized
					{'orderable': true}, // ISO code
					{'orderable': true}, // Fallback
					{'orderable': true}, // Visible
					{'orderable': false} // Actions
				]
			});

			$('#languages-index-datatable_wrapper .table-caption').text('{{ array_get($uiTranslations, 'languages') }}');
			$('#languages-index-datatable_wrapper .dataTables_filter input').attr('placeholder', '{{ array_get($uiTranslations, 'search') }}');
		}

		$('body').on('click', '.translations-in-database-confirm-action', function (e) {
			e.preventDefault();

			var btn = $(this);

			swal({
				title: $(btn).data('title'),
				text: $(btn).data('text'),
				type: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#DD6B55',
				confirmButtonText: $(btn).data('confirm-button-text')
			}).then(function () {
				$.ajax({
					url: btn.attr('href'),
					type: btn.data('method'),
					dataType: 'json',

					success: function () {
						window.setTimeout(function () {
							swal({
								title: btn.data('success-title'),
								text: btn.data('success-text'),
								type: 'success'
							});
						}, 100);

						if (btn.data('id')) {
							var element = $('.object' + btn.data('id'));

							if (element.length) {
								element.fadeOut();
							} else {
								btn.closest('tr').fadeOut();
							}
						}
					},

					error: function (xhr) {
						console.log(xhr);
						window.setTimeout(function () {
							swal('Error', xhr.responseText, 'error');
						}, 100);
					}
				});
			}).catch(function () {});
		});
	});
</script>