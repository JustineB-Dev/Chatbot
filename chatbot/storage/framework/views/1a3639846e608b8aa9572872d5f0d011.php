<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Slash AI Chatbot</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

   
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">

    <link rel="stylesheet" href="<?php echo e(asset('chatbot.css')); ?>">
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
                <button type="button" id="send" class="send">Send</button>
            </div>
            </form>
        </div>

        <div class="container">
            <div id="chatbot-response"></div>
        </div>


        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $('#send').on('click', function () {
                var usrname = $('input[name="usrname"]').val();
                var comment = $('textarea[name="comment"]').val();

                $.ajax({
                    url: '/chat',
                    type: 'POST',
                    data: {
                        usrname: usrname,
                        comment: comment,
                        _token: '<?php echo e(csrf_token()); ?>'
                    },
                    success: function(response) {
                        console.log(response); // Log the response to check the output
                        if (response.response) {
                            $('#chatbot-response').text("Chatbot says: " + response.response);
                        } else {
                            $('#chatbot-response').text("Error: No response from OpenAI");
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("Error: ", error);
                        alert('An error occurred: ' + error);
                    }
                });
            });

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
<?php /**PATH C:\xampp\htdocs\dashboard\Chatbot\chatbot\resources\views/welcome.blade.php ENDPATH**/ ?>