<!doctype html>
<html>
  <head>
    <title>Let's Chat</title>

    <!-- bring in the jQuery library -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <!-- custom styles -->
    <style>
      #chat_log {
        display:inline-block;
        width: 500px;
        height: 300px;
      }
      .hidden {
        display: none;
      }
      #users {
        height: 300px;
        width: 100px;
      }
      #message {
        width: 470px;
      }
      * {
        font-family: Arial, Helvetica, sans-serif;
      }
      /* this is so the background image fills the whole screen */
      html {
        height:100%;
      }
      body {
        background-image: url("background.png");
        background-repeat: no-repeat;
        background-size: 100% 100%;
      }
      input[type=text], select {
        /* width: 100%; */
        width: 605px;
        padding: 12px 20px;
        margin: 8px 0;
        display: inline-block;
        border: 2px solid black;
        border-radius: 4px;
        box-sizing: border-box;
      }
      textarea {
        width: 100%;
        padding: 6px 8px;
        margin: 8px 0;
        display: inline-block;
        border: 2px solid black;
        border-radius: 4px;
        box-sizing: border-box;
      }
      button {
        /* width: 100%; */
        background-color: #1982FC;
        color: white;
        padding: 14px 20px;
        margin: 8px 0;
        border: none;
        border-radius: 4px;
        cursor: pointer;
      }
      button:hover {
        background-color: skyblue;
      }
      /* make anchor look like a button */
      a {
        display: block;
        width: 100px;
        height: 20px;
        background-color: #1982FC;
        padding: 10px;
        text-align: center;
        border-radius: 4px;
        color: white;
        text-decoration: none;
        margin-top: 5px;
        margin-bottom: 5px;
        font-size: 14px;
      }
    </style>
  </head>
  <body>
    <h1>Let's Chat</h1>

    <?php
      // if ($_COOKIE['loggedin']) {
      if ( isset($_COOKIE['PHPSESSID']) ) {
        // start up the session (make available any variables in the $_SESSION superglobal)
        session_start();
    ?>
        <div>You are logged in as: <?php print $_SESSION['username']; ?></div>
        <!-- <br> -->
        <a href="logout.php">Log Out</a>
        <!-- <br> -->

        <div id="error"></div>
        
        <select id="chat_room">
          <option value="1">Chat Room 1</option>
          <option value="2">Chat Room 2</option>
        </select>

        <div id="panel_chat">
          <textarea readonly id="users"></textarea>
          <textarea readonly id="chat_log"></textarea>
          <br>
          <input type="text" id="message">
          <button id="button_send">Send Message</button>
          <p>Note: Restricted words are "apple" and "pear"</p>
        </div>

    <?php
      }
      else if ($_GET['loginError']) {
    ?>
        <div>Invalid credentials, please try again</div>
    <?php
      }
      // this is not used now because the input tag has the "required" property
      else if ($_GET['registerError']) {
    ?>
      <div>Error: username and password cannot be empty!</div>
    <?php    
      }
      else {
    ?>
      <form id="panel_name" action="login.php">
        Name: &nbsp; &nbsp; &nbsp; &nbsp; <input type="text" name="username" id="username" required>
        <br>
        Password: &nbsp; <input type="text" name="password" id="password" required>
        <br>
        <button id="register" name="register">Register</button>
        <button id="login" name="login">Login</button>
      </form>
    <?php
      }
    ?>

    <!-- login system doesn't allow name change -->
    <!-- <div id="change_name" class="hidden">
      <input type="text" id="newname">
      <button id="button_change">Change Name</button>
    </div> -->

    <script>
      // let selectedName;
      $(document).ready(function() {
        // DOM refs
        let panel_name = document.getElementById('panel_name');
        let username = document.getElementById('username');
        let register = document.getElementById('register');
        let panel_chat = document.getElementById('panel_chat');
        let chat_log = document.getElementById('chat_log');
        let message = document.getElementById('message');
        let button_send = document.getElementById('button_send');

        let error = document.getElementById('error');
        let change_name = document.getElementById('change_name');
        let newname = document.getElementById('newname');
        let button_change = document.getElementById('button_change');
        let chat_room = document.getElementById('chat_room');

        let users = document.getElementById('users');

        let usernameFromPHP = '<?php print $_SESSION['username']; ?>'

        //when we switch the chat room, update data immediately
        //data is updated via an interval later in the code, but this improves UX 
        if (chat_room) {
          chat_room.onchange = function() {
            getData();
          }
        }

        //list of restricted words
        let words = [];

        //get restricted words from the server
        function getWords() {
          $.ajax({
            url: 'get_words.php',
            type: 'get',
            data: { //no specific criteria, get all data
              //
            },
            success: function(data, status) {
              let parsed = JSON.parse(data);
              for (let i = 0; i < parsed.length; i++) {
                words.push(parsed[i].word);
              }
            }
          })
        }      

        if (button_send) {
          //send user's message to the server
          button_send.addEventListener('click', function() {
            //check for restricted words
            for (let i = 0; i < words.length; i++) {
              if (message.value.includes(words[i])) {
                error.innerHTML = "Error: Restricted word!";
                return; //exit function, do not make the ajax call
              }
            }
            error.innerHTML = ""; //if valid word, remove error message

            // make an ajax call to the server to save the message
            $.ajax({
              url: 'save_message.php',
              type: 'post',
              data: {
                name: usernameFromPHP,
                message: message.value,
                chat_room: parseInt(chat_room.value)
              },
              //when it's successful we should add the message to the chat log so we can see it
              success: function(data, status) {
                chat_log.value += usernameFromPHP + ': ' + message.value + "\n";
              }
            });
          });

        }

        //get user's message from the server
        function getData() {
          $.ajax({
            url: 'get_messages.php',
            type: 'get',
            data: { //send chat room to get messages for only that chat
              name: usernameFromPHP,
              chat_room: parseInt(chat_room.value)
            },
            success: function(data, status) {
              let parsed = JSON.parse(data);
              let newChatroom = '';
              for (let i = 0; i < parsed.length; i++) {
                newChatroom += parsed[i].name + ': ' + parsed[i].message + "\n";
              }
              chat_log.value = newChatroom;
              setTimeout(getData, 2000); //update the data every 2 seconds
            }
          })
        }

        //get online users
        function getUsers() {
          console.log('getUsers() runnning');
          $.ajax({
            url: 'get_users.php',
            type: 'get',
            data: { 
              //
            },
            success: function(data, status) {
              let parsed = JSON.parse(data);
              console.log("users online:");
              console.log(parsed);
              for (let i = 0; i < parsed.length; i++) {
                if (!users.innerHTML.includes(parsed[i].name)) { //prevent duplicates
                  users.innerHTML += "🟢" + parsed[i].name + "\n"; //needs to be += because = will only use the last parsed[i]
                }
              }
              setTimeout(getUsers, 2000); //update the data every 2 seconds
            }
          })
        }

        //get offline users
        function getUsersOffline() {
          console.log('getUsersOffline() runnning');
          $.ajax({
            url: 'get_users_offline.php',
            type: 'get',
            data: { 
              //
            },
            success: function(data, status) {
              let parsed = JSON.parse(data);
              console.log("users offline:");
              console.log(parsed);
              for (let i = 0; i < parsed.length; i++) {
                if (!users.innerHTML.includes(parsed[i].name)) { //prevent duplicates
                  users.innerHTML += "🔴" + parsed[i].name + "\n"; //needs to be += because = will only use the last parsed[i]
                }
              }
              setTimeout(getUsersOffline, 2000); //update the data every 2 seconds
            }
          })
        }

        // why are there 3 apis?
        // http://ip-api.com/json is not https, cannot call from some clients, can call from server
        // https://ipinfo.io/json has a low call limit
        // https://geolocation-db.com/json/ is reliable but has less information  

        function getAPI_server() {
          $.ajax({
            type: "GET",
            url: "api.php",
            success: function(api, status) {
              const data = JSON.parse(api);
              console.log("api from server:");
              console.log(data);
            },
            error: function(request, data, status) {
              console.log('AJAX Error');
            }  
          });
        }

        function getAPI_server2() {
          $.ajax({
            type: "GET",
            url: "api2.php",
            success: function(api, status) {
              const data = JSON.parse(api);
              console.log("api from server2:");
              console.log(data);
            },
            error: function(request, data, status) {
              console.log('AJAX Error');
            }  
          });
        }

        function getAPI_server3() {
          $.ajax({
            type: "GET",
            url: "api3.php",
            success: function(api, status) {
              const data = JSON.parse(api);
              console.log("api from server3:");
              console.log(data);
            },
            error: function(request, data, status) {
              console.log('AJAX Error');
            }  
          });
        }

        function getAPI_client() {
          $.ajax({
            type: "GET",
            url: "http://ip-api.com/json",
            success: function(api, status) {
              const data = (api);
              console.log("api from client:");
              console.log(data);
            },
            error: function(request, data, status) {
              console.log('AJAX Error');
            }  
          });
        }

        function getAPI_client2() {
          $.ajax({
            type: "GET",
            url: "https://ipinfo.io/json",
            success: function(api, status) {
              const data = (api);
              console.log("api from client2:");
              console.log(data);
            },
            error: function(request, data, status) {
              console.log('AJAX Error');
            }  
          });
        }

        function getAPI_client3() {
          $.ajax({
            type: "GET",
            url: "https://geolocation-db.com/json/",
            success: function(api, status) {
              const data = (api);
              console.log("api from client3:");
              console.log(data);
            },
            error: function(request, data, status) {
              console.log('AJAX Error');
            }  
          });
        }

        if (chat_room) {
          getWords();
          getData();
          getUsers();
          getUsersOffline(); 
          getAPI_server();
          getAPI_client();
          getAPI_server2();
          getAPI_client2();
          getAPI_server3();
          getAPI_client3();
        }

      });

    </script>

  </body>
</html>