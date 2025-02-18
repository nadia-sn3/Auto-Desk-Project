document.addEventListener('DOMContentLoaded', function() {
    const uploadBtn = document.getElementById('uploadBtn');
    const uploadModal = document.getElementById('uploadModal');
    const closeBtn = document.querySelector('.close');
    const dropArea = document.getElementById('dropArea');
    const fileInput = document.getElementById('fileInput');
    const fileList = document.getElementById('fileList');
    const confirmUploadBtn = document.getElementById('confirmUploadBtn');

    let selectedFiles = [];

    uploadBtn.addEventListener('click', () => {
        uploadModal.style.display = 'flex';
    });

    closeBtn.addEventListener('click', () => {
        uploadModal.style.display = 'none';
    });

    window.addEventListener('click', (event) => {
        if (event.target === uploadModal) {
            uploadModal.style.display = 'none';
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
        for (let i = 0; i < files.length; i++) {
            selectedFiles.push(files[i]);
        }
        updateFileList();
    }

    function updateFileList() {
        fileList.innerHTML = '';

        selectedFiles.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.classList.add('file-item');
            fileItem.innerHTML = `
                ${file.name} (${(file.size / 1024).toFixed(2)} KB)
                <button class="delete-btn" data-index="${index}">&times;</button>
            `;
            fileList.appendChild(fileItem);
        });

        confirmUploadBtn.style.display = selectedFiles.length > 0 ? 'inline-block' : 'none';

        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const index = this.getAttribute('data-index');
                selectedFiles.splice(index, 1);
                updateFileList();
            });
        });
    }

    confirmUploadBtn.addEventListener('click', function() {
        alert('Files ready to be uploaded: ' + selectedFiles.map(f => f.name).join(', '));
    });
});
