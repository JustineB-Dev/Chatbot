<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Slash AI Chatbot</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

   
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">

    <link rel="stylesheet" href="{{ asset('chatbot.css') }}">
</head>
<body>

    <div class="navbar">
        <h1>SLASH AI CHATBOT</h1>
    </div>

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
                <button type="reset">Reset</button>
                <button type="sent">Sent</button>
            </div>
            </form>
        </div>


          <script>
        document.addEventListener('DOMContentLoaded', function () {
            const firstTextBox = document.querySelector('input[name="usrname"]');
            const secondTextArea = document.querySelector('textarea[name="comment"]');
            
           
            secondTextArea.disabled = true;

          
            firstTextBox.addEventListener('input', function () {
                if (firstTextBox.value.trim() !== '') {
                    secondTextArea.disabled = false; 
                } else {
                    secondTextArea.disabled = true; 
                    secondTextArea.value = '';
                }
            });

           
            secondTextArea.addEventListener('input', function () {
                if (secondTextArea.disabled) {
                    secondTextArea.value = '';
                }
            });
        });

    </script>

</body>
</html>
