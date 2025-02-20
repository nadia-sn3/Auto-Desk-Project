document.addEventListener('DOMContentLoaded', function() {
    const uploadBtn = document.getElementById('uploadBtn');
    const uploadModal = document.getElementById('uploadModal');
    const closeBtn = document.querySelector('.close');
    const dropArea = document.getElementById('dropArea');
    const fileInput = document.getElementById('fileInput');
    const fileList = document.getElementById('fileList');
    const confirmUploadBtn = document.getElementById('confirmUploadBtn');
    const commitMessageContainer = document.getElementById('commitMessageContainer');
    const commitMessageInput = document.getElementById('commitMessage');

    let selectedFiles = [];

    uploadBtn.addEventListener('click', () => {
        uploadModal.style.display = 'flex';
    });

    closeBtn.addEventListener('click', () => {
        uploadModal.style.display = 'none';
        resetForm();
    });

    window.addEventListener('click', (event) => {
        if (event.target === uploadModal) {
            uploadModal.style.display = 'none';
            resetForm();
        }
    });

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, () => dropArea.classList.add('highlight'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, () => dropArea.classList.remove('highlight'), false);
    });

    dropArea.addEventListener('drop', handleDrop, false);

    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }

    function handleFiles(files) {
        selectedFiles = []; // Reset selected files
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            if (file.name.endsWith('.obj')) {
                selectedFiles.push(file);
            } else {
                alert(`File "${file.name}" is not a valid .obj file. Only .obj files are allowed.`);
            }
        }
        updateFileList();
    }

    function updateFileList() {
        fileList.innerHTML = '';

        if (selectedFiles.length > 0) {
            selectedFiles.forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.classList.add('file-item');
                fileItem.innerHTML = `
                    ${file.name} (${(file.size / 1024).toFixed(2)} KB)
                    <button class="delete-btn" data-index="${index}">&times;</button>
                `;
                fileList.appendChild(fileItem);
            });

            commitMessageContainer.style.display = 'block'; 
            confirmUploadBtn.style.display = 'inline-block'; 
        } else {
            commitMessageContainer.style.display = 'none';
            confirmUploadBtn.style.display = 'none';
        }

        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const index = this.getAttribute('data-index');
                selectedFiles.splice(index, 1);
                updateFileList();
            });
        });
    }

    commitMessageInput.addEventListener('input', function() {
        confirmUploadBtn.disabled = !commitMessageInput.value.trim();
    });

    confirmUploadBtn.addEventListener('click', function() {
        if (selectedFiles.length > 0 && commitMessageInput.value.trim()) {
            const commitMessage = commitMessageInput.value.trim();
            alert(`Files ready to be uploaded: ${selectedFiles.map(f => f.name).join(', ')}\nCommit Message: ${commitMessage}`);
            resetForm();
        }
    });

    function resetForm() {
        selectedFiles = [];
        fileList.innerHTML = '';
        commitMessageInput.value = '';
        commitMessageContainer.style.display = 'none';
        confirmUploadBtn.style.display = 'none';
        confirmUploadBtn.disabled = true;
    }
});
