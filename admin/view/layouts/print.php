<html>
    <head>
        <link href = "../css/container.css" rel = "stylesheet" type = "text/css" />
        <style type = "text/css">
            body {
                background: #FFFFFF;
            }
            
            .print-only { display: block; }
            .no-print { display: none; }
        </style>
        <script type = "text/javascript">
            function populate() {
                var contentArea = document.getElementById('content');
                var parentArea = window.opener.document.getElementById('content');
                
                contentArea.innerHTML = parentArea.innerHTML;
                window.print();
            }
        </script>
    </head>
    <body onload = "populate()">             
        <div id = "content">                                            
        </div>
    </body>
</html>
