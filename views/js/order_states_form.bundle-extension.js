$(() => {
  const sendWarehouseCheckbox = '#order_state_send_email_warehouse';
  const emailWarehouseFieldSelect = '.order_state_warehouse_email_select';
  const emailWarehouseField = '#order_state_email_warehouse';
  const emailWarehouseTemplateField = '.order_state_warehouse_template_select';
  const emailWarehouseTemplatePreview = '#order_state_warehouse_template_preview';

  let warehouseTemplatePreviewWindow = null;
  function viewTemplates($uri) {
    if (warehouseTemplatePreviewWindow != null && !warehouseTemplatePreviewWindow.closed) {
      warehouseTemplatePreviewWindow.close();
    }
    warehouseTemplatePreviewWindow = window.open(
      $uri,
      'tpl_viewing',
      'toolbar=0,'
      + 'location=0,'
      + 'directories=0,'
      + 'statfr=no,'
      + 'menubar=0,'
      + 'scrollbars=yes,'
      + 'resizable=yes,'
      + 'width=520,'
      + 'height=400,'
      + 'top=50,'
      + 'left=300',
    );
    if (warehouseTemplatePreviewWindow) {
      warehouseTemplatePreviewWindow.focus();
    }
  }

  if (!$(sendWarehouseCheckbox).is(':checked')) {
    $(emailWarehouseTemplateField).hide();
    $(emailWarehouseFieldSelect).hide();
  } else {
    $(emailWarehouseField).prop('required', true);
  }

  $(document).on('change', sendWarehouseCheckbox, () => {
    $(emailWarehouseTemplateField).slideToggle();
    $(emailWarehouseFieldSelect).slideToggle();
    $(emailWarehouseField).prop('required', !$(emailWarehouseField).prop('required'));
  });

  $(document).on('click', emailWarehouseTemplatePreview, (event) => {
    const $element = $(event.currentTarget);
    const $select = $element
      .closest('.form-group')
      .find('select.translatable_choice:visible');
    const $uri = $select.find('option:selected').attr('data-preview');

    viewTemplates($uri);
  });
});