const modal = document.getElementById("deleteModal");
const closeBtn = document.getElementsByClassName("close-btn")[0];
const cancelDeleteBtn = document.getElementById("cancelDelete");
const confirmDeleteBtn = document.getElementById("confirmDelete");

let userIdToDelete = null;

function confirmDelete(event, userId) {
    event.preventDefault(); 
    userIdToDelete = userId;
    modal.style.display = "block";
}

closeBtn.onclick = function() {
    modal.style.display = "none";
};

cancelDeleteBtn.onclick = function() {
    modal.style.display = "none";
};

confirmDeleteBtn.onclick = function() {
    if (userIdToDelete) {
        window.location.href = "delete-user.php?id=" + userIdToDelete;
    }
};

window.onclick = function(event) {
    if (event.target === modal) {
        modal.style.display = "none";
    }
};

document.querySelector('.open-modal-btn').addEventListener('click', function() {
    document.getElementById('deleteModal').style.display = 'block';
});

document.querySelector('.close-btn').addEventListener('click', function() {
    document.getElementById('deleteModal').style.display = 'none';
});
