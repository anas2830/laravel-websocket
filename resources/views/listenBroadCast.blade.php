<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    <link rel="stylesheet" href="{{ asset('css/app.css')}}" >
</head>
<body>
    
    <div id="app">
        
    </div>

    <script src="{{ asset('js/app.js')}}"></script>

    <script type="text/javascript">
        new Vue({
            el: "#app",
            created(){
                Echo.private('testChannel')
                    .listen('taskEvent', (e) => {
                        console.log(e);
                    });
            }
        });
    </script>
</body>
</html>
