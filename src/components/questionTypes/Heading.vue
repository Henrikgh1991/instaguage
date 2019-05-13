<template>
    <div class="question heading">
        <div v-if="mode == 'query'" class="query-view" :class="{ invalid : !allOK }">
            <h3>{{ questionData.title }}</h3>
        </div> <!-- query -->

        <div v-if="mode == 'edit'">
            <div class="form-group" :class="{ invalid : (questionData.title.length == 0) }">
                <label class="selected">Title</label>
                <input 
                    class="form-control"
                    v-model="questionData.title" 
                    v-focus
                    placeholder="heading" 
                    type="text">
            </div>
        </div> <!-- edit -->

        <div v-if="mode == 'result'">
            <h3>{{ questionData.title }}</h3>
        </div> <!-- result -->

    </div>
</template>

<style lang="scss" scoped>
h3 {
    font-weight:bold;
}

.question.heading {
    .invalid {
        input {
            border:solid 1px #900;
            background-color:#fee;
        }
    }
}
</style>

<script>
export default {
    name : "Heading",
    questionMetadata : {
        type : "heading",
        title : "heading (structural element)"
    },    
    data : function() {
        return {
        }
    },
    created : function() {
        this.onResultChanged(this.getResult());
    },
    computed : {
        allOK : function() {
            return true;
        },
        runningId : {
            get() { return this.$store.state.runningId; },
            set(v) { this.$store.commit('setRunningId', v); }
        }
    },
    methods : {
        checkValidity : function(questionData) {
            return (questionData.title.trim().length > 0);
        },
        newQuestionData : function() {
            return {
                type : "heading",
                title : "",
                id : (++this.runningId)
            };
        },
        getResult : function() {            
            return {
                ok : this.allOK,
                value : this.questionData
            }
        },
    },
    watch : {
        questionData : {
            deep : true,
            handler : function(newV, oldV) {
                this.onResultChanged(this.getResult());
            }
        },
    },
    props : {
        optional : {
            default : true
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
                    title : ""
                };
            }
        }
    },
    components : {
    }
}
</script>