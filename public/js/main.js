document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.action-btn.delete');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            const confirmation = confirm('Êtes-vous sûr de vouloir supprimer cet élément ?');
            if (!confirmation) {
                event.preventDefault();
            }
        });
    });
});
