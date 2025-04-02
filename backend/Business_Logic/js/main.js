var viewer;  
let md_ViewerDocument = null; 
let md_viewables = [];  

var options = {
    env: 'AutodeskProduction2',
    accessToken: accessToken,
};

Autodesk.Viewing.Initializer(options, () => {
    // Check if the viewer is already initialized
    if (!viewer) {
        viewer = new Autodesk.Viewing.GuiViewer3D(document.getElementById("forgeViewer"));
        viewer.start();
    }

    document.querySelectorAll(".file-link").forEach(link => {
        link.addEventListener("click", async (e) => {
            e.preventDefault();
            const fileType = link.getAttribute("data-file-type");
            const fileName = link.getAttribute("data-file-name");
            const urn = link.getAttribute("data-urn");
            const object_key = link.getAttribute("data-object-key");

        
            if (fileType === 'obj' || fileType === 'dwg' || fileType === 'stl') {
                
                const imageContainer = document.getElementById('imageContainer');
                // imageContainer.style.visibility = 'hidden';  
                const forgeViewer = document.getElementById('forgeViewer');
                // imageContainer.style.zIndex = '2';
                // forgeViewer.style.zIndex = '1';
                imageContainer.classList.remove('show');
                forgeViewer.style.visibility = 'visible';
                forgeViewer.style.opacity = '1';
                if (urn) {
                    try {
                        console.log("Starting translation for URN:", urn);
                        const isTranslated = await startTranslation(urn);

                        if (isTranslated) {
                            console.log("Translation started. Checking status...");
                            // Check the translation status after a delay
                            setTimeout(() => {
                                checkTranslationStatus(urn);
                            }, 5000); // Adjust delay as needed
                        } else {
                            console.log("Translation failed to start.");
                        }
                    } catch (error) {
                        console.error("Error during translation process:", error);
                    }
                }
            }
            else
            {
                await tempDownloadFile(object_key, fileName);
            }

            // Check for image file types (jpg, jpeg, png)
            if (fileType === 'jpg' || fileType === 'jpeg' || fileType === 'png') {
                // Hide the Forge viewer and show the image container
                const forgeViewer = document.getElementById('forgeViewer');
                //forgeViewer.style.visibility = 'hidden';  // Hide Forge viewer
                const imageContainer = document.getElementById('imageContainer');
                // imageContainer.style.visibility = 'visible';  // Show image container
                // forgeViewer.style.zIndex = '2';
                // imageContainer.style.zIndex = '1';
                imageContainer.classList.add('show');
                forgeViewer.style.visibility = 'hidden';
                forgeViewer.style.opacity = '0';
                // Assuming the file is already saved in the server's uploads directory, extract its path
                const filePath = 'http://localhost/Auto-Desk-Project/backend/Business_Logic/Uploaded_Process/uploads/' + fileName;  // Adjust path accordingly

                // Create an image element
                const imageElement = document.createElement('img');
                imageElement.src = filePath; // Set the image source
                imageElement.style.maxWidth = '100%'; // Ensure the image fits within its container
                imageElement.style.maxHeight = 'calc(100vh - 100px)'; // Ensure the image fits within the viewport
                imageElement.style.objectFit = 'contain'; // Maintain aspect ratio

                // Clear previous image and append the new image
                imageContainer.innerHTML = '';  // Clear any existing content
                imageContainer.appendChild(imageElement); // Append the new image element

                console.log('Image saved and accessible at:', filePath);
            } 

            
            // Check for PDF, DOCX, or DOC files
            else if (fileType === 'pdf' || fileType === 'docx' || fileType === 'doc') {
                const forgeViewer = document.getElementById('forgeViewer');
                //forgeViewer.style.visibility = 'hidden';  // Hide Forge viewer

                const imageContainer = document.getElementById('imageContainer');
                imageContainer.classList.add('show');
                forgeViewer.style.visibility = 'hidden';
                forgeViewer.style.opacity = '0';
                const fileDownloadPath = `http://localhost/Auto-Desk-Project/backend/Business_Logic/Function/download.php?file=${encodeURIComponent(decodeURIComponent(fileName))}`;

                // Create a download button
                imageContainer.innerHTML = '';  // Clear previous content
                const downloadBtn = document.createElement('a');
                downloadBtn.href = fileDownloadPath;
                downloadBtn.innerText = 'Download File';
                downloadBtn.style.display = 'block';
                downloadBtn.style.padding = '10px';
                downloadBtn.style.background = '#007bff';
                downloadBtn.style.color = 'white';
                downloadBtn.style.textAlign = 'center';
                downloadBtn.style.textDecoration = 'none';
                downloadBtn.style.borderRadius = '5px';
                downloadBtn.setAttribute('download', fileName);

                imageContainer.appendChild(downloadBtn);  // Append button

                // PDF rendering logic
                if (fileType === 'pdf') {
                    const pdfPath = `http://localhost/Auto-Desk-Project/backend/Business_Logic/Function/download.php?file=${fileName}`;
                    const loadingTask = pdfjsLib.getDocument(pdfPath);

                    loadingTask.promise.then(function(pdf) {
                        console.log('PDF loaded');

                        // Clear previous pages (if any)
                        imageContainer.innerHTML = '';  // Clear any previous content

                        // Render all pages of the PDF
                        for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                            pdf.getPage(pageNum).then(function(page) {
                                console.log('Rendering page: ' + pageNum);

                                const scale = 1.5;  // Adjust scale as needed
                                const viewport = page.getViewport({ scale: scale });

                                // Create a new canvas for this page
                                const canvas = document.createElement('canvas');
                                imageContainer.appendChild(canvas);  // Append the canvas to the container

                                const context = canvas.getContext('2d');
                                canvas.width = viewport.width;
                                canvas.height = viewport.height;

                                // Render the page on the canvas
                                page.render({
                                    canvasContext: context,
                                    viewport: viewport
                                }).promise.then(function() {
                                    console.log('Page rendered');
                                });
                            });
                        }
                    }).catch(function(error) {
                        console.error('Error loading PDF: ' + error);
                    });

                    console.log('Download link displayed:', fileDownloadPath);
                } 
                // DOCX or DOC file rendering logic
                else if (fileType === 'docx' || fileType === 'doc') {
                    // Construct the path to the .docx file served by your PHP backend
    const docxPath = `http://localhost/Auto-Desk-Project/backend/Business_Logic/Function/download.php?file=${encodeURIComponent(decodeURIComponent(fileName))}`;
    
    // Fetch the .docx file
    fetch(docxPath)
        .then(response => response.arrayBuffer())  // Fetch the file as an array
        .then(arrayBuffer => {
            console.log('Word document loaded');

            // Convert the .docx file into HTML using Mammoth.js
            mammoth.convertToHtml({ arrayBuffer: arrayBuffer })
                .then(function(result) {
                    // Clear previous content
                    imageContainer.innerHTML = '';  // Clear any previous content
                    
                    // Insert the generated HTML into your container
                    imageContainer.innerHTML = result.value;  // The HTML content from the .docx file
                    
                    console.log('Word document rendered');
                })
                .catch(function(error) {
                    console.error('Error converting .docx to HTML:', error);
                });
            })
                .catch(function(error) {
                    console.error('Error loading .docx file:', error);
                });
        
            console.log('Download link displayed:', fileDownloadPath);
            }
                
            }else if (fileType === 'video' || fileType === 'mp4' || fileType === 'webm' || fileType === 'ogg') {
                const videoPath = `http://localhost/Auto-Desk-Project/backend/Business_Logic/Function/download.php?file=${encodeURIComponent(decodeURIComponent(fileName))}`;
            
                // Clear previous content
                imageContainer.innerHTML = '';
            
                // Create a video element
                const videoElement = document.createElement('video');
                videoElement.width = 600;
                videoElement.controls = true;  // Enable video controls
            
                // Create multiple sources for different video formats
                const videoSourceMP4 = document.createElement('source');
                videoSourceMP4.src = videoPath;
                videoSourceMP4.type = 'video/mp4';
            
                const videoSourceWebM = document.createElement('source');
                videoSourceWebM.src = videoPath.replace('.mp4', '.webm');  // Update if your server has a webm version
                videoSourceWebM.type = 'video/webm';
            
                const videoSourceOGG = document.createElement('source');
                videoSourceOGG.src = videoPath.replace('.mp4', '.ogg');  // Update if your server has an ogg version
                videoSourceOGG.type = 'video/ogg';
            
                // Append the sources to the video element
                videoElement.appendChild(videoSourceMP4);
                videoElement.appendChild(videoSourceWebM);
                videoElement.appendChild(videoSourceOGG);
            
                // Append the video element to the container
                imageContainer.appendChild(videoElement);
            
                console.log('Video rendered');
            }
            else {
                console.log('Unsupported file type:', fileType);
            }
        });
    });
});

async function tempDownloadFile(object_key, file_name) 
{
    try {
        const response = await fetch(`http://localhost/Auto-Desk-Project/backend/Business_Logic/Function/temp_download.php?objectKey=${object_key}&fileName=${file_name}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            }
            // body: new URLSearchParams({
            //     'objectKey': object_key,
            //     'fileName': file_name
            // })
        });
    } catch (error) {
        console.error('Failed to call start_downloading.php:', error);
    }
}


// async function startTranslation(urn) {
//     try {
//         const response = await fetch(`http://localhost/Backend/Auto-Desk-Project/backend/Business_Logic/Function/start_translation.php?urn=${urn}`, {
//             method: 'GET',
//             headers: {
//                 'Content-Type': 'application/x-www-form-urlencoded',
//             },
//             body: new URLSearchParams({
//                 'urn': urn
//             })
//         });

//         // Get the raw response text
//         const responseText = await response.text();
//         console.log('Raw server response:', responseText);  // Log raw response for debugging

//         // Remove the comment and any other non-JSON content
//         const cleanResponseText = responseText.replace(/\/\/.*$/g, '').trim();  // Regex to remove comments

//         // Attempt to parse the cleaned response text as JSON
//         const data = JSON.parse(cleanResponseText);

//         // Check if the response is successful
//         if (data.status == 'success') {
//             console.log('Translation started:', data.result);
//             return data.result;
//         } else {
//             console.error('Error starting translation:', data.message || data.error || 'Unknown error');
//         }
//     } catch (error) {
//         console.error('Failed to call start_translation.php:', error);
//     }
// }


async function startTranslation(urn) {
    try {
        // Construct the full URL with query parameters
        const url = `http://localhost/Auto-Desk-Project/backend/Business_Logic/Function/start_translation.php?urn=${encodeURIComponent(urn)}`;
        
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            }
        });

        // Get the raw response text
        const responseText = await response.text();
        console.log('Raw server response:', responseText);  // Log raw response for debugging

        // Remove any comment and trim the response
        const cleanResponseText = responseText.replace(/\/\/.*$/g, '').trim();  

        // Attempt to parse the cleaned response text as JSON
        const data = JSON.parse(cleanResponseText);

        // Check if the response is successful
        if (data.status == 'success') {
            console.log('Translation started:', data.result);
            return data.result;
        } else {
            console.error('Error starting translation:', data.message || data.error || 'Unknown error');
        }
    } catch (error) {
        console.error('Failed to call start_translation.php:', error);
    }
}




async function checkTranslationStatus(urn) {
    try {
        const response = await fetch(`http://localhost/Auto-Desk-Project/backend/Business_Logic/Function/check_job_status.php?urn=${urn}`, {
            method: 'GET',
        });

        const responseText = await response.text();
        console.log('Translation Status Response:', responseText);

        const cleanResponseText = responseText.replace(/\/\/.*$/g, '').trim();  // Clean response text

        const statusData = JSON.parse(cleanResponseText);

        if (statusData.status === 'success') {
            const result = JSON.parse(statusData.result);  // The result is a stringified JSON
            console.log('Translation is complete:', result);

            // Check if the progress is complete
            if (result.progress === 'complete') {
                console.log('Translation completed successfully.');
               
                loadModel(urn); // Call loadModel with the urn
            } else {
                console.log('Translation is still in progress...');
            }
        } else {
            console.error('Error checking translation status:', statusData.error || 'Unknown error');
        }
    } catch (error) {
        console.error('Failed to check translation status:', error);
    }
}

async function loadModel(urn) {
    if (urn && urn.length > 0) {
        const documentId = `urn:${urn}`;
        console.log('Loading model with URN:', urn);
        Autodesk.Viewing.Document.load(documentId, onDocumentLoadSuccess, onDocumentLoadFailure);
    } else {
        console.error('Error: URN is undefined or invalid.');
    }
}

function onDocumentLoadSuccess(doc) {
    console.log('Document loaded successfully:', doc);

    const viewables = doc.getRoot().search({ type: 'geometry' });

    if (viewables.length === 0) {
        console.error('No viewables found.');
        return;
    }

    // Load the first available viewable from the document
    viewer.loadDocumentNode(doc, viewables[0]).then(() => {
        console.log('Model loaded successfully and displayed.');
    }).catch((error) => {
        console.error('Error loading model:', error);
    });
}



function onDocumentLoadFailure(errorCode) {
    console.error('Failed fetching Forge manifest. Error code:', errorCode);
    switch (errorCode) {
        case 5:
            console.error("Error: Model access issue, possibly permissions-related.");
            break;
        default:
            console.error("Unknown error occurred.");
            break;
    }
}

