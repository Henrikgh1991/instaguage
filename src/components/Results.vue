<template>
	<main>
		<section>
			<div class="alert alert-danger" role="alert" v-if="errormsg">
				{{ errormsg }}
			</div>

			<div class="container">
				<div class="row" v-if="survey">
					<div class="col-md-12" >
                        <result-display :survey="survey"></result-display>
					</div> <!-- col -->
				</div> <!-- row -->
			</div> <!-- container -->
		</section>
	</main>
</template>

<style lang="scss">
</style>
  
<script>
import $ from 'jquery'
import ResultDisplay from './ResultDisplay.vue'
import MqttConnection from './MqttConnection.vue'

export default {
	name : "Results",
	data : function() {
	     return {
			 mqttClient : null,
			 clientID : null,
             survey : null,
			 errormsg : null,
	     }
    },
	created : function() {		
		var me = this;
		me.clientID = Math.random().toString(36).substr(2, 10);
        this.loadData();
    },	
	methods : {
		initMqtt : function() {
			var me = this;

			console.log("try init mqtt: %s", me.clientID);

			me.mqttClient = MqttConnection.init(me.clientID);

			me.mqttClient.on("connect", () => {
				console.log("mqtt connected");
				me.mqttClient.subscribe("instagauge/" + me.survey._key);
			});

			me.mqttClient.on("message", (topic, message) => { 			
				var s = message.toString();
				try {		
					var m = JSON.parse(s);
					if((m.type == "survey") && (m.survey._key == me.survey._key)) {
						if(!(m.survey.timestamp && me.survey.timestamp && (me.survey.timestamp >= m.survey.timestamp))) {
							me.survey = m.survey;
						}
					}
				} catch($e) {
					console.log("err: %o", $e);
				}
			});
		},
        loadData : function() {
            var me = this;
            me.$http.get("surveys/" + me.$route.params.id + "/results").then((d) => {
                d = d.data;
                if(d.ok) {
                    me.survey = d.survey;
					if(me.survey.instantResults) {
						me.initMqtt();
					}
                } else {
					me.errormsg = d.msg;
				}
            });
        },
	},
	mounted : function() {
	},
	watch : {
	},
	components : {
        ResultDisplay
	}
}
</script>
