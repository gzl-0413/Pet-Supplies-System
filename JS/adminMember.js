$(document).ready(function() {
    $('.toggle-status').on('change', function() {
        var memberId = $(this).data('id');
        var newStatus = $(this).is(':checked') ? 'Verified' : 'Block';

        $.ajax({
            url: 'adminMemberUpdateStatus.php',
            method: 'POST',
            data: {
                member_id: memberId,
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
        alert("Successfully added member with ID: " + successAdd);
    } else if (successEdit) {
        alert("Successfully edited member with ID: " + successEdit);
    }
});