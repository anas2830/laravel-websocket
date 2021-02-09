<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Example Component</div>

                    <div class="card-body">
                        I'm an example component.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script type="text/javascript">
   
   import axios from 'axios';

    let api_url='http://127.0.0.1:8000/api/test/';

   

    export default {

        mounted() 
        {
            // axios.get(api_url)
            // .then((res) => {
            //     console.log(res);
            //     alert('hi');
            // })
            // .catch((error) => {
            //     console.log(error);
            // });

            // console.log('okk');
            
            // Echo.join(`chat-user`)
            //     .here((users) => {
            //         console.log(users);
            //     })
            //     .joining((user) => {
            //         console.log('join');
            //     })
            //     .leaving((user) => {
            //         console.log(user.name);
            // });


            Echo.private('testChannel')
                .listen('taskEvent', (e) => {
                    console.log(e);
                });

        }
    }
</script>

