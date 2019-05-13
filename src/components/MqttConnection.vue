<script>
import AWSMqtt from 'aws-mqtt-client'
import AppConfig from '../app-config.js'

export default {
    name : "MqttConnection",
    init : function(clientID) {
		this.clientID = clientID;

		this.mqttClient = new AWSMqtt(AppConfig.mqtt);		
		
		return this.mqttClient;
    }
}
</script>