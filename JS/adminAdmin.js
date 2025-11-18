$(document).ready(function() {
    $('.toggle-status').on('change', function() {
        var adminId = $(this).data('id');
        var newStatus = $(this).is(':checked') ? 'Active' : 'Block';

        $.ajax({
            url: 'adminAdminUpdateStatus.php',
            method: 'POST',
            data: {
                admin_id: adminId,
                status: newStatus
            },
            dataType: 'json',
            success: function(response) {
                console.log(response);

                if (response.success === true) {
                    alert('Status updated successfully');
                } else {
                    alert('Failed to update status');
                }
            },
            error: function() {
                alert('Error updating status');
            }
        });
    });

    const urlParams = new URLSearchParams(window.location.search);
        
    const successAdd = urlParams.get('success_add');
    const successEdit = urlParams.get('success_edit');
    
    if (successAdd) {
        alert("Successfully added admin with ID: " + successAdd);
    } else if (successEdit) {
        alert("Successfully edited admin with ID: " + successEdit);
    }
});

