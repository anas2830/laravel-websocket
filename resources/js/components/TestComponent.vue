<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">test Component</div>

                    <div class="card-body">
                        I'm an test component.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script type="text/javascript">

    export default {
        mounted() 
        {
            Echo.channel('testChannel')
                .listen('taskEvent', (e) => {
                    console.log(e);
            });
        }
    }
</script>

