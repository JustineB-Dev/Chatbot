<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Slash AI Chatbot</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

   
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
    <link rel="stylesheet" href="{{ asset('chatbot.css') }}">
</head>
<body>

    <div class="navbar">
        <h1>SLASH AI CHATBOT</h1>
    </div>
      
      <div class="chat-wrapper">
        <!-- Left Side: RAG Function -->
        <aside class="chat-aside">
            <div class="container">
              <h2>RAG Function</h2>
              <h3>Upload PDF</h3>
              <form action="/upload-pdf" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="pdfFiles[]" accept="application/pdf" multiple required id="pdfFiles" onchange="displayFileNames()">
                <button type="submit">Upload</button>
              </form>
              <div id="fileListContainer"></div>
            </div>
          </aside>
        <!-- Right Side: Slash AI Chatbot -->
        <main class="chat-main">
          <div class="container">
            <h2>Slash Ai-Chatbot</h2>
            <form class="form" id="usrform">
              <div class="input-usrname">
                <input type="text" name="usrname" placeholder="Enter your name..." required>
              </div>
      
              <div class="input-comment">
                <textarea name="comment" placeholder="Ask Chatbot..." form="usrform" required></textarea>
              </div>
      
              <div class="button-group">
                <button class="reset" type="reset">Reset</button>
                <button class="sent" type="button" id="sendMessage">Send</button>
              </div>
            </form>
      
            <div class="container" style="margin-top: 30px; background-color: rgba(172, 163, 163, 0.5);">
              <h4> AI Response: </h4>
              <div id="chatResponse" style="margin-top: 10px; font-weight: bold;"></div>
            </div>
          </div>
        </main>
      </div>
      
      <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sendBtn = document.getElementById('sendMessage');
    
            sendBtn.addEventListener('click', function () {
                const username = document.querySelector('input[name="usrname"]').value;
                const question = document.querySelector('textarea[name="comment"]').value;
                const chatResponse = document.getElementById('chatResponse');
    
                if (!username || !question) {
                    alert("Please enter both your name and a question.");
                    return;
                }
    
                fetch("/chatbot/message", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        username: username,
                        message: question
                    })
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Langflow response:', data); // Debugging log to check the full response
    
                    // Access the message from the correct path
                    const outputs = data.outputs; // Assuming 'outputs' is a property of 'data'
                    const message = outputs?.[0]?.outputs?.[0]?.messages?.[0]?.message;
    
                    if (message) {
                        chatResponse.innerText = `AI says: ${message}`;
                    } else {
                        chatResponse.innerText = "No response from AI.";
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    chatResponse.innerText = "Error sending message.";
                });
            });
        });
    
        // Display selected file names
        function displayFileNames() {
            const fileInput = document.getElementById('pdfFiles');
            const fileListContainer = document.getElementById('fileListContainer');
    
            const files = fileInput.files;
            if (files.length === 0) {
                fileListContainer.innerHTML = '';
                return;
            }
    
            let fileListHTML = '<h4>Files selected:</h4><ul>';
            for (let i = 0; i < files.length; i++) {
                fileListHTML += `<li>${files[i].name}</li>`;
            }
            fileListHTML += '</ul>';
            fileListContainer.innerHTML = fileListHTML;
        }
    
        // Handle PDF upload form submission
        const uploadForm = document.querySelector('form[action="/upload-pdf"]');
        uploadForm.addEventListener('submit', function (e) {
            e.preventDefault();
    
            const formData = new FormData(uploadForm);
            const fileListContainer = document.getElementById('fileListContainer');
            
            // Show uploading message
            fileListContainer.innerHTML = `<p style="color: blue;">Uploading and replacing old files...</p>`;
    
            fetch("/upload-pdf", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('Upload failed');
                return response.text();
            })
            .then(() => {
              document.getElementById('pdfFiles').value = ''; // Reset input field
              fileListContainer.innerHTML = `<p style="color: green;">✅ PDF(s) uploaded successfully and previous files replaced.</p>`;

              // ✅ Trigger Langflow Ingestion
              fetch("http://127.0.0.1:7860/api/v1/run/335c1045-6531-49cf-9368-3ae17359f2e3?stream=false", {
                  method: "POST",
                  headers: {
                      "Content-Type": "application/json"
                  },
                  body: JSON.stringify({
                      input_value: "",
                      output_type: "text",
                      input_type: "text",
                      tweaks: {
                          "Directory-aMlxQ": {},
                          "SplitText-p3Lxr": {},
                          "FAISS-xWfkL": {},
                          "OllamaEmbeddings-pK6q7": {}
                      }
                  })
              })
              .then(res => res.json())
              .then(data => {
                  console.log("✅ Langflow Response:", data);
                  fileListContainer.innerHTML += `<p style="color: green;">✅ Langflow ingestion completed.</p>`;
              })
              .catch(err => {
                  console.error("❌ Langflow Error:", err);
                  fileListContainer.innerHTML += `<p style="color: red;">❌ Langflow ingestion failed. Check console for details.</p>`;
              });
            })
            .catch(() => {
                // Show error message
                fileListContainer.innerHTML = `<p style="color: red;">❌ Upload failed. Please try again.</p>`;
            });
        });
    </script>
    

</body>
</html>
