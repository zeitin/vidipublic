<html>
    <head>
        <title>Text Chat</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> 
        <link rel="stylesheet" type="text/css" media="all" href="style.css" />
    </head>
    <body>
        <div id="login_form_container">
            <h1>Text Chat</h1>
            <hr /> 
            <form action="chat.php" method="get">
                <p>Adını söyle:<br />
                <input type="text" id="name" name="name" /></p>
                <p>Bir oda seç:<br />
                <input type="text" id="room" name="room" /></p>
                <p>Boyutları ne olsun:<br />
                <input type="text" id="width" name="width" size="4" value="500"/> x
                <input type="text" id="height" name="height" size="4" value="400"/></p>
                <input type="submit" value="Başla" />
            </form>
        </div>
    </body>
</html>


