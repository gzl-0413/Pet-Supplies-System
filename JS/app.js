// $(document).ready(function () {
//     // Handle form submission
//     $('form').on('submit', function (e) {
//         e.preventDefault();
//         var $form = $(this);
//         var action = $form.attr('action');

//         $.ajax({
//             url: action,
//             type: 'POST',
//             data: new FormData(this),
//             processData: false,
//             contentType: false,
//             success: function (response) {
//                 $('.flash-message').remove();
//                 var message = $('<div class="flash-message">' + response.message + '</div>');
//                 $('body').prepend(message);
//                 setTimeout(function () {
//                     message.fadeOut(function () {
//                         $(this).remove();
//                     });
//                 }, 3000);
//                 if (response.redirect) {
//                     window.location.href = response.redirect;
//                 }
//             }
//         });
//     });

//     // Handle delete confirmation
//     $(document).on('click', 'button[data-post]', function () {
//         if (confirm('Are you sure?')) {
//             var url = $(this).data('post');
//             $.post(url, function (response) {
//                 location.reload();
//             });
//         }
//     });
// });

$('[data-confirm]').on('click', e => {
    const text = e.target.dataset.confirm || 'Are you sure?';
    if(!confirm(text)){
    e.preventDefault();
    e.stopImmediatePropagation();
    }
    });

// ============================================================================
// Page Load (jQuery)
// ============================================================================

$(() => {

    // Autofocus
    $('form :input:not(button):first').focus();
    $('.err:first').prev().focus();
    $('.err:first').prev().find(':input:first').focus();
    
    // Confirmation message
    $('[data-confirm]').on('click', e => {
        const text = e.target.dataset.confirm || 'Are you sure?';
        if (!confirm(text)) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    });

    // Initiate GET request
    $('[data-get]').on('click', e => {
        e.preventDefault();
        const url = e.target.dataset.get;
        location = url || location;
    });

    // Initiate POST request
    $('[data-post]').on('click', e => {
        e.preventDefault();
        const url = e.target.dataset.post;
        const f = $('<form>').appendTo(document.body)[0];
        f.method = 'POST';
        f.action = url || location;
        f.submit();
    });

    // Reset form
    $('[type=reset]').on('click', e => {
        e.preventDefault();
        location = location;
    });

    // Auto uppercase
    $('[data-upper]').on('input', e => {
        const a = e.target.selectionStart;
        const b = e.target.selectionEnd;
        e.target.value = e.target.value.toUpperCase();
        e.target.setSelectionRange(a, b);
    });

    // Photo preview
    $('label.upload input[type=file]').on('change', e => {
        const f = e.target.files[0];
        const img = $(e.target).siblings('img')[0];

        if (!img) return;

        img.dataset.src ??= img.src;

        if (f?.type.startsWith('image/')) {
            img.src = URL.createObjectURL(f);
        }
        else {
            img.src = img.dataset.src;
            e.target.value = '';
        }
    });

    // Drag & drop
    $('label.upload').on('dragenter dragover dragleave drop', e => {
        e.preventDefault();
        e.stopPropagation();
    })

    .on('drop', function(e) {
        const f = e.originalEvent.dataTransfer.files[0];
        const img = $(this).find('img')[0];
        const input = $(this).find('input[type=file]')[0];
    
        if (!img) return;
    
        img.dataset.src ??= img.src;
    
        if (f?.type.startsWith('image/')) {
            img.src = URL.createObjectURL(f);
            input.files = e.originalEvent.dataTransfer.files;
        }
        else {
            img.src = img.dataset.src;
            input.value = '';
        }
    });

});

function updateCategoryDropdown() {
    $.ajax({
        url: '/categories',
        type: 'GET',
        success: function (response) {
            var $dropdown = $('#category-dropdown');
            $dropdown.empty();
            response.categories.forEach(function (category) {
                $dropdown.append('<option value="' + category.id + '">' + category.name + '</option>');
            });
        }
    });
}
 // Record Listing (Table View + Photo View)
 $('#view-toggle button').on('click', function () {
    var view = $(this).data('view');
    $('.record-list').hide();
    $('#' + view + '-view').show();
    $('#view-toggle button').removeClass('active');
    $(this).addClass('active');
});

// Product Filtering
$('#filter-form').on('submit', function (e) {
    e.preventDefault();
    var $form = $(this);

    $.ajax({
        url: $form.attr('action'),
        type: 'GET',
        data: $form.serialize(),
        success: function (response) {
            $('#product-list').html(response.productListHtml);
        }
    });
});

// Filtering, Sorting, and Paging
$('#filter-sort-paginate-form').on('submit', function (e) {
    e.preventDefault();
    var $form = $(this);

    $.ajax({
        url: $form.attr('action'),
        type: 'GET',
        data: $form.serialize(),
        success: function (response) {
            $('#product-list').html(response.productListHtml);
            $('#pagination').html(response.paginationHtml);
        }
    });
});

// Multiple Photos Upload
$('#multiple-photos-upload').on('change', function () {
    var files = this.files;
    var $previewContainer = $('#photo-preview');
    $previewContainer.empty();

    Array.from(files).forEach(function (file) {
        var reader = new FileReader();
        reader.onload = function (e) {
            var imgHtml = '<img src="' + e.target.result + '" alt="' + file.name + '">';
            $previewContainer.append(imgHtml);
        };
        reader.readAsDataURL(file);
    });
});

// Drag-and-Drop Photo Upload
var $dragDropArea = $('#drag-drop-area');

$dragDropArea.on('dragover', function (e) {
    e.preventDefault();
    $(this).addClass('drag-over');
}).on('dragleave', function () {
    $(this).removeClass('drag-over');
}).on('drop', function (e) {
    e.preventDefault();
    $(this).removeClass('drag-over');
    var files = e.originalEvent.dataTransfer.files;

    Array.from(files).forEach(function (file) {
        var reader = new FileReader();
        reader.onload = function (e) {
            var imgHtml = '<img src="' + e.target.result + '" alt="' + file.name + '">';
            $('#photo-preview').append(imgHtml);
        };
        reader.readAsDataURL(file);
    });
});

// Batch Insertion/Updating
$('#batch-upload-form').on('submit', function (e) {
    e.preventDefault();
    var $form = $(this);

    $.ajax({
        url: $form.attr('action'),
        type: 'POST',
        data: new FormData(this),
        processData: false,
        contentType: false,
        success: function (response) {
            alert(response.message);
            if (response.redirect) {
                window.location.href = response.redirect;
            }
        }
    });
});

// Batch Deletion
$('#batch-delete-form').on('submit', function (e) {
    e.preventDefault();
    var $form = $(this);

    if (confirm('Are you sure you want to delete selected items?')) {
        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            success: function (response) {
                alert(response.message);
                if (response.redirect) {
                    window.location.href = response.redirect;
                }
            }
        });
    }
});

// Delete Confirmation Dialogs
$(document).on('click', '.confirm-delete', function (e) {
    e.preventDefault();
    var url = $(this).attr('href');

    if (confirm('Are you sure you want to delete this item?')) {
        $.post(url, function (response) {
            location.reload();
        });
    }
});

// Handle Dynamic Addition/Removal of Product Fields
$(document).on('click', '#add-product-field', function () {
    var newField = `
        <div class="product-field">
            <label>Product Name:</label>
            <input type="text" name="product_name[]" required>
            <label>Product Price:</label>
            <input type="number" name="product_price[]" required>
            <button type="button" class="remove-field">Remove</button>
        </div>
    `;
    $('#product-fields-container').append(newField);
});

$(document).on('click', '.remove-field', function () {
    $(this).closest('.product-field').remove();
});
