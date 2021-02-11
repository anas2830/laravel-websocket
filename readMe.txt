use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class taskEvent implements ShouldBroadcastNow ( for public boradcas )

public function broadcastOn()
    	public function broadcastOn()
    {
        return new Channel('testChannel');
    }

event(new reqInfoReceiveEvent($evenet));

<script type="text/javascript">
        new Vue({
            el: "#app",
            created(){
                console.log('okk');

                // Echo.channel('testChannel')
                //     .listen('taskEvent', (e) => {
                //         console.log(e);
                //     });

                window.Echo.channel('testChannel')
                .listen('taskEvent', (e) => {
                    console.log("Received Data: ");
                    console.log(e);
                });
            }
        });
    </script>