<html>
    <head>
        <style>
            @page{
                margin-top: 100px; /* create space for header */
                margin-bottom: 70px; /* create space for footer */
            }
            header, footer{
                position: fixed;
                left: 0px;
                right: 0px;
            }
            header{
                height: 60px;
                margin-top: -60px;
            }
            footer{
                height: 50px;
                margin-bottom: -50px;
              }
        </style>
    </head>
    <body>
        <header>
            <h1>This is a Header</h1>
        </header>
        <footer>
            <p>Copyright &copy; <?php echo date("Y");?></p>
        </footer>
        <main>
            <p>This is the body</p>
        </main>
    </body>
</html>