<template>
	<main>
        <modal ref="modal">
            <span slot="title">{{ dlg.title }}</span>
            <span slot="content">{{ dlg.content }}</span>
            <span slot="footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
                <button type="button" 
                    @click="onYesButtonClicked"
                    class="btn btn-primary" data-dismiss="modal">Yes</button>
            </span>
        </modal>

		<section>
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<h1>Details</h1>
					</div>
				</div>

				<div class="row" v-if="survey">                  
					<div class="col-md-12">
                        <figure class="qr-code pull-right" title="Participant QR code">
                            <img
                                v-if="qrCodeUrl"
                                class="img-responsive" 
                                :src="qrCodeUrl">
                        </figure>

                        <p>
                            <dfn title="Upper- and lowercase letters are treated distinctively">Case-sensitive</dfn> survey key:
                        </p>

                        <p>
                            <span class="pretty-key">{{ prettyKey(survey._key) }}</span>
                        </p>

                        <p>
                            Created at: {{ moment(survey.createdAt).format("LL") }}
                            <br/>
                            Available until: {{ moment(survey.createdAt).add(6, 'months').format("LL") }}
                        </p>

                        <div class="power-switch">
                            <div @click.stop="toggleVoting" 
                                :class="{ disabled : toggling }"
                                class="toggle-switch voting-enabled" v-if="survey.votingDisabled !== true"
                                title="voting enabled">
                                <font-awesome-icon icon="toggle-on"  />
                                voting enabled
                            </div>

                            <div @click.stop="toggleVoting" 
                                :class="{ disabled : toggling }"
                                class="toggle-switch voting-disabled"  v-if="survey.votingDisabled === true"
                                title="voting disabled">
                                <font-awesome-icon icon="toggle-off"  />
                                voting disabled
                            </div>
                        </div>

                        <p>
                            Participant link:
                            <router-link target="_blank" :to="{ name : 'Survey', params : { id : survey._key }}">
                                {{ "https://" + window.location.host + $router.resolve({ name : 'Survey', params : { id : survey._key } }).href }}
                            </router-link>

                            <br>

                            Embedding link:
                            <router-link target="_blank" :to="{ name : 'Embed', params : { id : survey._key }}">
                                {{ "https://" + window.location.host + $router.resolve({ name : 'Embed', params : { id : survey._key } }).href }}
                            </router-link>

                            <br>

                            Result link:
                            <router-link target="_blank" :to="{ name : 'Results', params : { id : survey._key }}">
                                {{ "https://" + window.location.host + $router.resolve({ name : 'Results', params : { id : survey._key } }).href }}
                            </router-link>

                            <br>

                            Admin link:
                            <router-link target="_blank" :to="{ name : 'Details', params : { id : survey._key, pw : $route.params.pw }}">
                                {{ "https://" + window.location.host + $router.resolve({ name : 'Details', params : { id : survey._key, pw : $route.params.pw } }).href }}
                            </router-link>

                        </p>
					</div>


                    <div class="col-md-12">
                        <result-display :survey="survey" style="overflow:auto;"></result-display>

                        <div class="clearfix">
                            <button type="button"
                                :disabled="dataLoading"
                                @click.stop="loadData"
                                class="btn btn-primary">
                                <font-awesome-icon icon="sync" v-if="!dataLoading" />
                                <font-awesome-icon icon="spinner" pulse v-if="dataLoading" />
                                refresh!
                            </button>

                            <button type="button"
                                class="btn btn-default"
                                @click.stop="exportResults">
                                <font-awesome-icon icon="file-excel" />
                                export results!
                            </button>

                            <button type="button"
                                class="btn btn-default"
                                @click.stop="exportSheets">
                                <font-awesome-icon icon="file-code" />
                                export sheets!
                            </button>

                            <button type="button"
                                @click.stop="confirmClone"
                                title="clone this survey!"
                                class="btn btn-default pull-right">
                                <font-awesome-icon icon="clone" />
                                clone!
                            </button>
                        </div>
                    </div>
				</div>
			</div>
		</section>
	</main>

</template>

<style lang="scss" scoped>
.pretty-key {
    font-family:monospace;
    font-weight:bold;
    font-size:200%;
}

.qr-code {
    //width:2.54cm;
}

.alert-warning {
    a {
        color:inherit;
        text-decoration: underline;
    }
}

.toggle-switch {
    cursor:pointer; 

    &.disabled {
        opacity:0.5;
        cursor:default;
    }

    &.voting-enabled {
        color:green;
    }

    &.voting-disabled {
        color:#900;
    }
}

.power-switch {
    margin-bottom:1em;
}
</style>
  
<script>
import $ from 'jquery'
import ResultDisplay from './ResultDisplay.vue'

import moment from 'moment'
import Modal from './Modal.vue'
import QRCode from 'qrcode'

export default {
	name : "Details",
	data : function() {
	     return {
             survey : null,
             dataLoading : false,
             qrCodeUrl : null,
             toggling : false,
             window : window,
             dlg : {
                 title : "",
                 content : "",
                 type : null
             }
	     }
	},
	created : function() {
        this.loadData();
	},	
	methods : {
        exportSheets() {
            var me = this;

            me.$http.post("surveys/" + me.$route.params.id + "/generateAccessToken", {}, { headers : { "X-SURVEY-PW" : me.$route.params.pw }}).then((d) => {
                d = d.data;
                if(d.ok) {
                    var url = me.$http.options.root + '/surveys/' + me.survey._key + '/sheetData?accessToken=' + encodeURIComponent(d.item);
                    window.location.href = url;
                }
            });
        },
        exportResults() {
            var me = this;

            me.$http.post("surveys/" + me.$route.params.id + "/generateAccessToken", {}, { headers : { "X-SURVEY-PW" : me.$route.params.pw }}).then((d) => {
                d = d.data;
                if(d.ok) {
                    var url = me.$http.options.root + '/surveys/' + me.survey._key + '/exportResults?accessToken=' + encodeURIComponent(d.item);
                    window.location.href = url;
                }
            });
        },
        toggleVoting() {
            var me = this;
            if(me.toggling) return;
            me.toggling = true;
            me.$http.post("surveys/" + me.$route.params.id + "/toggleVoting", {}, { headers : { "X-SURVEY-PW" : me.$route.params.pw }}).then((d) => {
                d = d.data;
                if(d.ok) {
                    me.loadData(d.survey);
                }
                me.toggling = false;
            });            
        },
        cloneSurvey() {
            var me = this;
            me.$http.post("surveys/" + me.$route.params.id + "/clone", {}, { headers : { "X-SURVEY-PW" : me.$route.params.pw }}).then((d) => {
                d = d.data;
                if(d.ok) {
                    me.$router.push({ name : 'AdminSurveyEdit', params : { id : d.survey._key } });
                }
            });            
        },
        onYesButtonClicked() {
            switch(this.dlg.type) {
            case "clone":
                this.dlg.type = null;
                this.cloneSurvey();
                break;
            }
            this.dlg.type = null;
        },
        confirmClone() {
            this.dlg.title = "Are you sure?";
            this.dlg.content = "Do you really want to create a new survey like this one?";
            this.dlg.type = "clone";
            this.$refs.modal.show();
        },
        moment,
        loadData : function(survey) {
            var me = this;

            me.dataLoading = true;

            me.$http.post("votes/forceRefresh", {
                surveyId : me.$route.params.id
            });

            if(survey) {
                me.survey = survey;
            } else {
                me.$http.get("surveys/" + me.$route.params.id + "/details", { headers : { "X-SURVEY-PW" : me.$route.params.pw }}).then((d) => {
                    d = d.data;
                    me.dataLoading = false;
                    if(d.ok) {
                        me.survey = d.survey;
                    }
                });
            }
        },
        prettyKey : function(k) {
            var retval = [];
            for(var i = k.length - 1; i >= 0; i--) {
                retval.push(k[i]);
                if(i % 2 == 0) {
                    retval.push(" ");
                }
            }
            return retval.reverse().join("");
        }
	},
	mounted : function() {
	},
    computed : {
    },
	watch : { 
        "survey"(newV) {
            if(!newV) return;
            var me = this;
            var url = 'https://' + window.location.host + this.$router.resolve({ name : 'Survey', params : { id : this.survey._key } }).href;
            QRCode.toDataURL(url, { errorCorrectionLevel: 'L', scale: 2 }, function(err, url) {
                me.qrCodeUrl = url;
            });
        },
        "$route.params.id"() {
            this.loadData();
        }
	},
	components : {
        ResultDisplay,
        Modal
	}
}
</script>
