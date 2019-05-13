<template>
	<main>
        <disclaimer />

        <modal ref="yesNoDlg">
            <span slot="title">{{ yesNoDlg.title }}</span>
            <span slot="content">{{ yesNoDlg.content }}</span>
            <span slot="footer">
                <button type="button"
                    @click.stop="$emit('yesNoDlg_chosen', true)"
                    class="btn btn-success"
                    title="OK">
                    Yes
                </button>
                <button type="button"
                    @click.stop="$emit('yesNoDlg_chosen', false)"
                    class="btn btn-default"
                    title="OK">
                    No
                </button>
            </span>
        </modal>

        <section>                
            <div class="container">
				<div class="row">
					<div class="col-md-12">
                        <h1>Create</h1>
                    </div>
                </div>

                <survey-editor :survey="survey" :state="editorState">
                    <div slot="advance-buttons" class="pull-right">
                        <button
                            :disabled="!editorState.allValid"
                            @click.stop="saveSurvey"
                            class="btn btn-primary">
                            <font-awesome-icon icon="cloud-upload-alt"/>
                            save&hellip;
                        </button>
                        <button
                            :disabled="!editorState.allValid"
                            @click.stop="publishSurvey"
                            class="btn btn-success">
                            <font-awesome-icon icon="handshake"/>
                            publish!
                        </button>
                    </div>
                </survey-editor>
            </div>
		</section>
	</main>
</template>
  
<script>
import SurveyEditor from './components/SurveyEditor.vue'
import Modal from './components/Modal.vue'
import Disclaimer from './components/Disclaimer.vue'

export default {
	name : "Home",
	data : function() {
	     return {
            editorState : { },
            yesNoDlg : {
                title : 'Are you sure?',
                content : "Do you really want to finalize this survey? Editing will not be possible any longer afterwards."
            },
            survey : {
                title : "",
                instantResults : true,
                allowMultiVote : false,
                immediateResultsAfterVote : true,
                allowResultsAfterVote : false,
                reloadAutomatically : false,
                status : "draft",
                questions : [
                    // { "type": "order", "title": "SortOrder", "id": 1, "options": [ { "id": 2, "title": "A" }, { "id": 3, "title": "B" }, { "id": 4, "title": "C" } ], "params": { } }
                    // { "type": "matrix", "title": "mq-i", "id": 1, "options": [ { "id": 2, "min": 1, "max": 10, "title": "width", "label": "X axis title" }, { "id": 3, "min": 1, "max": 10, "title": "height", "label": "Y axis title" } ], "items": [ { "id": 4, "title": "item a" }, { "id": 5, "title": "item b" } ], "params": { "mode": "items" } }
                    // { "type": "matrix", "title": "Matrix-Q", "id": 1, "options": [ { "id": 2, "min": 11, "max": 10, "title": "X Axis" }, { "id": 3, "min": 1, "max": 10, "title": "Y Axis" } ], "params": {} }
                    // { "type": "wordcloud", "title": "WordCloud", "id": 1, "options": [ { id:2, title:"" },{ id:3, title:"" }], "params": {} }
                    // { "type": "scale", "title": "", "id": 1, "options": [], "params": { "layout": "horizontal" } }
                ]
            }
	     }
    },
    methods : {
        saveSurvey() {
            var me = this;

            me.$http.post("surveys/create", { survey : me.survey }).then((d) => {
                d = d.data;
                if(d.ok) {
                    if(d.survey.status == 'draft') {
                        me.$router.push({ name : 'AdminSurveyEdit', params : { id : d.survey._key } });
                    } else {
                        me.$router.push({ name : 'Details', params : { id : d.survey._key, pw : d.survey.password } });
                    }
                } else {
                    alert(d.msg);
                }
            });
        },
        publishSurvey() {
            var me = this;

            me.$once("yesNoDlg_chosen", function(wasYes) {
                me.$refs.yesNoDlg.hide();
                if(!wasYes) return;
                me.survey.status = "final";
                me.saveSurvey();
            });

            me.$refs.yesNoDlg.show();
        },
    },
	components : {
        Modal,
        SurveyEditor,
        Disclaimer,
	}
}
</script>
