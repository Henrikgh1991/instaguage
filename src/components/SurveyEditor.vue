<template>
    <div class="row">
        <modal ref="typeChooserDlg">
            <span slot="title">Which type?</span>
            <span slot="content">
                <p>
                    Which question type do you want to add?
                </p>
                <select class="form-control" v-model="selectedQuestionType">
                    <option :value="null" disabled></option>
                    <option v-for="(qt,qtidx) in questionTypes" :value="qt" :key="qtidx">
                        {{ qt.questionMetadata.title }}
                    </option>
                </select>
            </span>
            <span slot="footer">
                <button type="button"
                    @click="addQuestion"
                    :disabled="selectedQuestionType == null"
                    data-dismiss="modal"
                    class="btn btn-success"
                    title="OK">
                    OK
                </button>
                <button type="button"
                    data-dismiss="modal"
                    class="btn btn-default"
                    title="Cancel">
                    Cancel
                </button>
            </span>
        </modal>

        <div class="col-md-12" v-if="survey">
            <div class="row">
                <div class="col-md-12">
                    <h2 v-if="preview">Preview</h2>

                    <p v-if="survey.questions.length == 0">
                        Your survey is empty; use the <a @click.stop="selectQuestionTypeToAdd"><em>add Question</em></a>-button below
                        to start adding questions or headings to structure your survey. When you think you're done, click the <em>preview</em>-button on the bottom right,
                        and when you're content,
                        use the <em>create</em>-button on that following page to obtain the survey details to pass on
                        to your participants.
                    </p>
                </div>
            </div>

            <div class="row" v-if="!preview">
                <div class="col-md-12 survey-title" v-if="survey.questions.length">
                    <div class="form-group">
                        <label class="selected">Survey title (will only be shown in your admin area)</label>
                        <input 
                            class="form-control"
                            v-model="survey.title" 
                            placeholder="" 
                            type="text">
                    </div>
                </div>

                <draggable v-model="survey.questions" :options="{ handle : '.drag-handle' }">
                    <div class="question-block col-md-12" v-for="(q, qidx) in survey.questions" :key="qidx">
                        <div class="row">
                            <div class="col-md-1 col-sm-2 col-xs-2">
                                <button type="button"
                                    title="Remove this question!"
                                    @click.stop="survey.questions.splice(qidx, 1)"
                                    class="btn btn-default btn-block delete-option-button">
                                    <font-awesome-icon icon="trash"/>
                                </button>
                            </div>
                            <div class="col-md-10 col-sm-8 col-xs-8">
                                <q-scale
                                    mode="edit"
                                    v-if="q.type == 'scale'"
                                    :on-result-changed="()=>{}"
                                    :question-data="q"></q-scale>

                                <q-multiple-choice
                                    mode="edit"
                                    v-if="q.type == 'multipleChoice'"
                                    :on-result-changed="()=>{}"
                                    :question-data="q"></q-multiple-choice>

                                <q-free-text
                                    mode="edit"
                                    v-if="q.type == 'freeText'"
                                    :on-result-changed="()=>{}"
                                    :question-data="q" />

                                <q-heading
                                    mode="edit"
                                    v-if="q.type == 'heading'"
                                    :on-result-changed="()=>{}"
                                    :question-data="q" />

                                <q-word-cloud
                                    mode="edit"
                                    v-if="q.type == 'wordcloud'"
                                    :on-result-changed="()=>{}"
                                    :question-data="q" />

                                <q-matrix
                                    mode="edit"
                                    v-if="q.type == 'matrix'"
                                    :on-result-changed="()=>{}"
                                    :question-data="q" />

                                <q-order
                                    mode="edit"
                                    v-if="q.type == 'order'"
                                    :on-result-changed="()=>{}"
                                    :question-data="q" />
                            </div>
                            <div class="col-md-1 col-sm-2 col-xs-2">
                                <div class="drag-handle btn btn-default btn-block" title="drag to change sort order">
                                    <font-awesome-icon icon="arrows-alt"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </draggable>

                <div v-if="survey.questions.length" class="col-md-12 options-block">

<!--
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" v-model="survey.noPublicResults" :value="true" :disabled="true">
                            no public results
                        </label>
                        <popper 
                            trigger="click"
                            :options="{placement: 'right'}"
                            class="explanation-toggle">

                            <div class="popover show">
                                <div class="popover-header">
                                    No Public Results
                                </div>

                                <div class="popover-body">
                                    Do not show results to
                                    anybody but the survey admin.
                                </div>
                            </div>

                            <font-awesome-icon icon="question-circle" slot="reference" />
                        </popper>
                    </div>
-->

                    <div class="radio">
                        <label>
                            <input type="radio" v-model="survey.immediateResultsAfterVote" :value="true" :disabled="survey.reloadAutomatically">
                            immediately show results after voting
                        </label>
                        <popper 
                            trigger="click"
                            :options="{placement: 'right'}"
                            class="explanation-toggle">

                            <div class="popover show">
                                <div class="popover-header">
                                    Immediate results
                                </div>

                                <div class="popover-body">
                                    After the user has cast his vote, 
                                    immediately show the current result of the poll. 
                                    <!--
                                    If unselected, just display a thank-you message.
                                    -->
                                </div>
                            </div>

                            <font-awesome-icon icon="question-circle" slot="reference" />
                        </popper>
                    </div>

                    <div class="radio">
                        <label>
                            <input type="radio" v-model="survey.allowResultsAfterVote" :value="true" :disabled="survey.reloadAutomatically">
                            show "thank you" page before redirecting to results
                            <!--
                            allow viewing the results after voting
                            -->
                        </label>

                        <popper 
                            trigger="click"
                            :options="{placement: 'right'}"
                            class="explanation-toggle">

                            <div class="popover show">
                                <div class="popover-header">
                                    Allow viewing results
                                </div>

                                <div class="popover-body">
                                    After the user has cast his vote, 
                                    show a thank-you message and a button 
                                    to show the current result of the poll. 
                                    <!--
                                    If unselected, only a thank-you message
                                    is being shown.
                                    -->
                                </div>
                            </div>

                            <font-awesome-icon icon="question-circle" slot="reference" />
                        </popper>
                    </div>

                    <div class="checkbox" :class="{ disabled : !instantResultsEnabled }">
                        <label>
                            <input type="checkbox" v-model="survey.instantResults" :value="true" :disabled="!instantResultsEnabled">
                            instant result update
                        </label>

                        <popper 
                            trigger="click"
                            :options="{placement: 'right'}"
                            class="explanation-toggle">

                            <div class="popover show">
                                <div class="popover-header">
                                    Instant update
                                </div>

                                <div class="popover-body">
                                    Don't just display the poll state once after
                                    voting, but keep showing updates continuously.
                                    If unchecked, there won't be automatic updates
                                    to the current poll state.
                                </div>
                            </div>

                            <font-awesome-icon icon="question-circle" slot="reference" />
                        </popper>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" v-model="survey.allowMultiVote" :value="true">
                            allow multiple votes from same browser
                        </label>

                        <popper
                            trigger="click"
                            :options="{placement: 'right'}"
                            class="explanation-toggle">

                            <div class="popover show">
                                <div class="popover-header">
                                    Multi Vote
                                </div>

                                <div class="popover-body">
                                    Disable the system that
                                    prevents multiple votes
                                    from the same browser (i.e.
                                    allow multiple votes to
                                    be cast); useful for
                                    public/shared computers.
                                </div>
                            </div>

                            <font-awesome-icon icon="question-circle" slot="reference" />
                        </popper>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" v-model="survey.reloadAutomatically" :value="true" :disabled="!survey.allowMultiVote">
                            restart survey automatically after voting
                        </label>

                        <popper
                            trigger="click"
                            :options="{placement: 'right'}"
                            class="explanation-toggle">

                            <div class="popover show">
                                <div class="popover-header">
                                    Reload Automatically
                                </div>

                                <div class="popover-body">
                                    When the user has finished voting,
                                    reload the survey so that
                                    others can vote again; useful
                                    for public/unattended devices.
                                </div>
                            </div>

                            <font-awesome-icon icon="question-circle" slot="reference" />
                        </popper>
                    </div>
                </div>

                <div class="col-md-12">
                    <button
                        type="button"
                        @click.stop="selectQuestionTypeToAdd" 
                        class="btn btn-primary">
                        <font-awesome-icon icon="plus"/>
                        add question!
                    </button>
                    <button
                        @click.stop="preview = true"
                        :disabled="(survey.questions.length == 0)"
                        type="button"
                        class="btn btn-default pull-right">
                        <font-awesome-icon icon="eye"/>
                        preview&hellip;
                    </button>
                </div> <!-- col -->
            </div> <!-- row !preview -->

            <div class="row" v-if="preview">
                <div class="question-block col-md-12" v-for="(q, qidx) in survey.questions" :key="qidx">
                    <q-scale
                        v-if="q.type == 'scale'"
                        :on-result-changed="()=>{}"
                        :question-data="q"></q-scale>
                    
                    <q-multiple-choice
                        v-if="q.type == 'multipleChoice'"
                        :on-result-changed="()=>{}"
                        :question-data="q"></q-multiple-choice>

                    <q-free-text
                        v-if="q.type == 'freeText'"
                        :on-result-changed="()=>{}"
                        :question-data="q"/>

                    <q-heading
                        v-if="q.type == 'heading'"
                        :on-result-changed="()=>{}"
                        :question-data="q"/>

                    <q-word-cloud
                        v-if="q.type == 'wordcloud'"
                        :on-result-changed="()=>{}"
                        :question-data="q"/>

                    <q-matrix
                        v-if="q.type == 'matrix'"
                        :on-result-changed="()=>{}"
                        :question-data="q"/>

                    <q-order
                        v-if="q.type == 'order'"
                        :on-result-changed="()=>{}"
                        :question-data="q"/>
                </div>

                <div class="col-md-12" v-if="!allValid && (survey.questions.length > 0)">
                    <div class="alert alert-warning" role="alert">
                        <p>
                            Your survey is incomplete and can't be created.
                        </p>

                        <p>
                            <small style="color:inherit;">
                            Please make sure that every question has a title, 
                            and that there's at least one option to choose from for single- and multiple choice
                            questions.
                            </small>
                        </p>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="alert alert-info" role="alert">
                        Remember to only publish unrestricted information and that results will be accessible to everyone knowing the survey's code!
                    </div>

                    <button
                        type="button"
                        @click.stop="preview = false" 
                        class="btn btn-default">
                        <font-awesome-icon icon="step-backward"/>
                        revise&hellip;
                    </button>
                    <slot name="advance-buttons">
                        <!--
                        <button
                            @click.stop="areYouSure"
                            :disabled="!allValid"
                            type="button"
                            class="btn btn-primary pull-right">
                            <font-awesome-icon icon="handshake"/>
                            create&hellip;
                        </button>
                        -->
                    </slot>
                </div> <!-- col -->

            </div> <!-- row preview -->
        </div>
    </div>
</template>

<style lang="scss" scoped>
.drag-handle {
    cursor:move;
    text-align:center;
    height:50px;
    line-height:40px;
}

.survey-title {
    border-bottom:solid 1px #999;
    margin-bottom:1em;
}

.alert-info {
    font-size:small;
    padding:0.5em;
}
</style>

<script>
import Modal from './Modal.vue'

import popper from 'vue-popperjs'
import draggable from 'vuedraggable'

import qScale from './questionTypes/Scale.vue'
import qMultipleChoice from './questionTypes/MultipleChoice.vue'
import qFreeText from './questionTypes/FreeText.vue'
import qHeading from './questionTypes/Heading.vue'
import qWordCloud from './questionTypes/WordCloud.vue'
import qMatrix from './questionTypes/Matrix.vue'
import qOrder from './questionTypes/Order.vue'

var questionTypes = [ qScale, qMultipleChoice, qFreeText, qHeading, qWordCloud, qMatrix, qOrder ];

export default {
    name : "SurveyEditor",
    data : function() {
        return  {
             questionTypes : questionTypes,
             questionTypeMap : {},
             selectedQuestionType : qScale,
             preview : false,
        };
    },
	created : function() {
        /*
        this.addQuestion();
        this.preview = true;
        */
        for(var i in this.questionTypes) {
            var qt = this.questionTypes[i];
            this.questionTypeMap[qt.questionMetadata.type] = qt;
        }
	},
    beforeDestroy : function() {
    },
    updated : function() {
    },
	mounted : function() {
        //this.$refs.typeChooserDlg.show();
	},
	methods : {
        updateRunningId() {
            if(!this.survey) return;
            var maxId = this.runningId;
            this.survey.questions.forEach(q => {
                maxId = Math.max(maxId, q.id);
                if(q.options) {
                    q.options.forEach(o => {
                        maxId = Math.max(maxId, o.id);
                    });
                }
            });
            this.runningId = maxId;
        },
        selectQuestionTypeToAdd : function() {
            this.$refs.typeChooserDlg.show();
        },
        /*
        createSurvey : function() {
            var me = this;

            me.$http.post("surveys/create", {
                survey : me.survey
            }).then((d) => {
                d = d.data;
                if(d.ok) {
                    me.$router.push({ name : "Details", params : { id : d.survey._key, pw : d.survey.password }});
                }
            });
        },
        */
        addQuestion : function() {
            this.survey.questions.push(this.selectedQuestionType.methods.newQuestionData.apply(this));
            this.selectedQuestionType = qScale;
        },
	},
    computed : {
        allValid : function() {
            var retval = (this.survey.questions.length > 0);

            for(var i in this.survey.questions) {
                var q = this.survey.questions[i];
                var qt = this.questionTypeMap[q.type];
                retval &= qt.methods.checkValidity.call(this, q);
            }

            if(this.state) {
                this.$set(this.state, "allValid", retval);
            }

            return retval;
        },
        instantResultsEnabled : function() {
            return this.survey.allowResultsAfterVote || this.survey.immediateResultsAfterVote;
        },
        runningId : {
            get() { return this.$store.state.runningId; },
            set(v) { this.$store.commit('setRunningId', v); }
        }
    },
	watch : {
        "survey.immediateResultsAfterVote" : function(newV) {
            if(newV) {
                this.survey.allowResultsAfterVote = false;
                this.survey.reloadAutomatically = false;
            }
            if(!newV && !this.survey.allowResultsAfterVote && !this.survey.reloadAutomatically) {
                //this.survey.instantResults = false;
                this.survey.allowResultsAfterVote = true;
            }
        },
        "survey.allowResultsAfterVote" : function(newV) {
            if(newV) {
                this.survey.immediateResultsAfterVote = false;
                this.survey.reloadAutomatically = false;
            }
            if(!newV && !this.survey.immediateResultsAfterVote && !this.survey.reloadAutomatically) {
                //this.survey.instantResults = false;
                this.survey.immediateResultsAfterVote = true;
            }
        },
        "survey.reloadAutomatically" : function(newV) {
            if(newV) {
                this.survey.allowResultsAfterVote = false;
                this.survey.instantResults = false;
                this.survey.immediateResultsAfterVote = false;
            } 
            
            if(!newV && !this.survey.immediateResultsAfterVote) {
                this.survey.immediateResultsAfterVote = true;
            }
        },
        "survey.allowMultiVote" : function(newV) {
            if(newV === false) {
                this.survey.reloadAutomatically = false;
            }
        },
        survey(newV) {
            if(!newV) return;
            this.updateRunningId();      
        },
	},
    props : {
        survey : null,
        state : null
    },
	components : {
        ...questionTypes.reduce((m,o) => { m["q" + o.name] = o; return m; }, {}),        
        popper,
        Modal,
        draggable,
	}
}
</script>