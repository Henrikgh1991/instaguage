<template>
    <div class="question wordcloud">

        <div v-if="mode == 'query'" class="panel panel-default query-view"  :class="layoutClasses">
            <div class="panel-heading">
                <div class="panel-title">
                    <h3>{{ questionData.title }}</h3>
                </div>
            </div>

            <div class="panel-body">
                <div v-if="int_header" 
                    v-html="int_header"
                    class="q-wordcloud-header q-wordcloud-content"></div>

                <div class="option-list">
                    <input type="text" class="form-control" placeholder="word cloud term" 
                        v-for="(i,iidx) in questionData.options" :key="iidx" v-model.trim="int_value[iidx].content">
                </div> 

                <div v-if="int_footer" 
                    v-html="int_footer"
                    class="q-wordcloud-footer q-wordcloud-content">
                </div>
            </div>
        </div> <!-- query -->

        <div v-if="mode == 'edit'">
            <div class="form-group" :class="{ invalid : (questionData.title.length == 0) }">
                <label class="selected">Title</label>
                <input 
                    class="form-control"
                    v-model="questionData.title" 
                    placeholder="word cloud title" 
                    v-focus
                    type="text">
            </div>

            <ol class="option-list">
                <li v-for="(o, oidx) in questionData.options" :key="oidx">
                    <div class="row">
                        <div class="col-md-11 col-sm-10 col-xs-10">
                            <div class="form-group">
                                <label class="selected">Option</label>
                                <input type="text" readonly class="form-control form-control-plaintext"
                                    :value="'Word Cloud answer option #' + (oidx+1)">
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-2 col-xs-2">
                            <button type="button"
                                title="Remove this option!"
                                @click.stop="questionData.options.splice(oidx, 1)"
                                class="btn btn-default btn-block delete-option-button">
                                <font-awesome-icon icon="trash"/>
                            </button>
                        </div>
                    </div>
                </li>
                <li>
                    <button type="button"
                        title="Add new option!"
                        @click.stop="addOption(questionData)"
                        class="btn btn-default">
                        <font-awesome-icon icon="plus"/>
                        add answer option!
                    </button>
                </li>
            </ol>    
        </div> <!-- edit -->

        <div v-if="mode == 'result'">
            <h3>{{ questionData.title }}</h3>
            <div class="embed-responsive embed-responsive-16by9">
                <vue-word-cloud
                    class="word-cloud"
                    fontFamily="SiemensSans"
                    :rotation="wordRotation"
                    :words="wordList"
                    :color="wordColor"
                />
            </div> 
            <p class="pull-right">Total: {{ questionData.votes.total }}</p>
        </div>

    </div>
</template>

<style lang="scss" scoped>
.question.wordcloud {
    .invalid {
        input {
            border:solid 1px #900;
            background-color:#fee;
        }
    }

    .query-view {
        .option-list {
            border:solid 1px transparent;
            padding:0.5em;

            input + input {
                margin-top:0.5em;
            }
        }
    }

    .word-cloud {
        position:absolute !important;
    }
    
    .query-view.invalid {
        .option-list {
            border:solid 1px #900;

            input {
                background-color:#fee;
            }

            border-radius:0.25em;
            background-color:#fee;
        }    
    }
}
</style>

<script>

import Modal from '../Modal.vue'
import VueWordCloud from 'vuewordcloud'
import Chance from 'chance'

export default {
    name : "WordCloud",
    questionMetadata : {
        type : "wordcloud",
        title : "word cloud"
    },
    data : function() {
        return {
            wordList : [
            ],
            optionsList : [],
            int_value : null,
            int_header : null,
            int_footer : null
        }
    },
    created : function() {
        var me = this;

        if(this.footer && this.footer.length) {
            this.int_footer = this.footer;
        }

        if(this.header && this.header.length) {
            this.int_header = this.header;
        }

        this.int_value = [];
        for(var idx  in this.questionData.options) {
            this.$set(this.int_value, idx, { id : this.questionData.options[idx].id, content : "" });
        }

        this.updateWordList();
        this.onResultChanged(this.getResult());
    },
    computed : {
        layoutClasses() {
            var retval = [];

            if(!this.allOK) retval.push("invalid");

            if(this.questionData.params && this.questionData.params.layout) {
                retval.push("layout-" + this.questionData.params.layout);
            }

            return retval.join(" ");
        },
        allOK : function() {
            if(this.optional) return true;

            for(var idx in this.int_value) {
                if(this.int_value[idx].content.length > 0) return true;
            }

            return false;
        },
        runningId : {
            get() { return this.$store.state.runningId; },
            set(v) { this.$store.commit('setRunningId', v); }
        }
    },
    methods : {
        updateWordList() {
            if(this.questionData && this.questionData.votes && this.questionData.votes.words) {
                var l = [];
                for(var k in this.questionData.votes.words) {
                    l.push([ k, this.questionData.votes.words[k] ]);
                }
                this.wordList = l;
            }
        },
        wordRotation(word) {
            var chance = new Chance(word[0]);
//            return chance.pickone([0, 1/8, 3/4, 7/8]);
            return chance.pickone([0, 3/4]);
        },
        wordColor(word) {
            return new Chance(word[0]).pickone( [
					'#d99cd1', '#c99cd1', '#b99cd1', '#a99cd1',
					'#403030', '#f97a7a',
					'#31a50d', '#d1b022', '#74482a',
					'#ffd077', '#3bc4c7', '#3a9eea', '#ff4e69', '#461e47'
				]);
        },
        optionLayoutClasses(o) {
            var retval = "";

            if(o.params && o.params.layout) {
                retval = o.params.layout;
            }

            return retval;
        },
        checkValidity : function(questionData) {
            return (questionData.title.trim().length > 0) && (questionData.options.length > 0);
        },
        newQuestionData : function() {
            return {
                type : "wordcloud",
                title : "",
                id : (++this.runningId),
                options : [
                    {
                        id : (++this.runningId),
                        title : ""
                    },
                    {
                        id : (++this.runningId),
                        title : ""
                    },
                    {
                        id : (++this.runningId),
                        title : ""
                    },
                 ],
                params : {
                }
            };
        },
        addOption : function(q) {
            q.options.push({
                id : (++this.runningId),
                title : ""
            });
        },
        getResult : function() {            
            return {
                ok : this.allOK,
                value : this.int_value
            }
        },
    },
    watch : {
        "questionData.votes.words"(newV) {
            this.updateWordList();
        },
        optionsList : function(newV, oldV) {
            this.int_value = null;
            this.onResultChanged(this.getResult());            
        },
        header : function(newV, oldV) {
            this.int_header = newV;
        },
        footer : function(newV, oldV) {
            this.int_footer = newV;
        },
        int_value : {
            deep : true,
            handler : function(newV, oldV) {
                this.onResultChanged(this.getResult());
            }
        },
    },
    props : {
        footer : {
            default : ""
        },
        header : {
            default : ""
        },
        optional : {
            default : false
        },
        onResultChanged : {
            type : Function,
            default : function(v) { }
        },
        mode : {
            default : "query"
        },
        questionData : {
            type : Object,
            default : function() {
                return {
                    title : "",
                    options : []
                };
            }
        }
    },
    components : {
        
        Modal,
        VueWordCloud
    }
}
</script>