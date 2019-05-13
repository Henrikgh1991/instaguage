<template>
	<main>
        <modal ref="infoDlg">
            <span slot="title">Info</span>
            <span slot="content">Survey saved!</span>
            <span slot="footer">
                <button type="button"
                    @click.stop="$refs.infoDlg.hide"
                    class="btn btn-success"
                    title="OK">
                    OK
                </button>
            </span>
        </modal>

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
                        <h1>Admin</h1>
                        <h2>Edit survey</h2>
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
import SurveyEditor from './../../SurveyEditor.vue'

import Modal from '../../Modal.vue'

export default {
    name : "AdminSurveyEdit",
    data : function() {
        return {
            survey : null,
            editorState : { },
            yesNoDlg : {
                title : 'Are you sure?',
                content : "Do you really want to finalize this survey? Editing will not be possible any longer afterwards."
            }
        };
    },
    created : function() {
        var me = this;

        me.$http.get("admin/surveys/" + me.$route.params.id + "/details").then((d) => {
            d = d.data;
            if(d.ok) {
                me.survey = d.item;
            } else {
                alert(d.msg);
            }
        });
    },
    methods : {
        saveSurvey() {
            var me = this;

            me.$http.put("admin/surveys/" + me.survey._key + "/details", { survey : me.survey }).then((d) => {
                d = d.data;
                if(d.ok) {
                    me.$refs.infoDlg.show();
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

                me.$http.post("admin/surveys/" + me.survey._key + "/details", { survey : me.survey }).then((d) => {
                    d = d.data;
                    if(d.ok) {
                        me.$router.push({ name : 'AdminStart' });
                    } else {
                        alert(d.msg);
                    }
                });
            });
            me.$refs.yesNoDlg.show();
        },
    },
    components : {
        SurveyEditor,
        
        Modal,
    }
}
</script>