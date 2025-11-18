<?php
 include_once '../includes/_base.php'; 
 auth();
$id = req('id');
$photo = req('photo');
$photo = basename($photo);
$notification = '';



if (is_post() && req('action') == 'delete') {
    try {
       
        
        if ($photo && file_exists("../uploads/$photo")) {
            unlink("uploads/$photo");
        }

       
        
        $stm = $_db->prepare('DELETE FROM product WHERE id = ?');
        if ($stm->execute([$id])) {
            $notification = 'Product deleted successfully!';
        } else {
            $notification = 'Error deleting product.';
        }
    } catch (Exception $e) {
        $notification = 'Error deleting product: ' . $e->getMessage();
    }

    temp('info', $notification);
    redirect('list.php');
}
?>
<!-- Confirmation Dialog -->
<div id="confirmation-dialog" class="modal hidden">
    <div class="modal-content">
        <span id="confirmation-message">Are you sure you want to delete this product?</span>
        <form method="post" id="confirmation-form">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
            <input type="hidden" name="photo" value="<?= htmlspecialchars($photo) ?>">
            <button type="submit">Yes</button>
            <button type="button" id="cancel-delete">No</button>
        </form>
    </div>
</div>
<?php display_notification($notification); ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Show confirmation dialog when delete is requested
        if (window.location.search.includes('confirm=true')) {
            document.getElementById('confirmation-dialog').classList.remove('hidden');
        }

       
        document.getElementById('cancel-delete').addEventListener('click', () => {
            document.getElementById('confirmation-dialog').classList.add('hidden');
            window.location.href = 'index.php'; // Redirect back to the index page
        });

      
        // document.getElementById('confirmation-form').addEventListener('submit', function (event) {
        //     event.preventDefault();
    
        // });
    });
</script>
 

<style>
    .modal {
        display: flex;
        align-items: center;
        justify-content: center;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
    }
    .modal-content {
        background: #fff;
        padding: 20px;
        border-radius: 5px;
        text-align: center;
    }
    .hidden {
        display: none;
    }
    .modal-content button {
        margin: 0 10px;
    }
</style>
