<template>
    <div v-if="survey" class="question-container" :class="surveyClasses(survey)">
        <div v-for="(q,qidx) in survey.questions" :key="qidx">
            <q-scale
                v-if="q.type == 'scale'"
                :question-data="q"
                mode="result" />

            <q-multiple-choice
                v-if="q.type == 'multipleChoice'"
                :question-data="q"
                mode="result" />

            <q-free-text
                v-if="q.type == 'freeText'"
                :question-data="q"
                mode="result" />

            <q-heading
                v-if="q.type == 'heading'"
                :question-data="q"
                mode="result" />

            <q-word-cloud
                v-if="q.type == 'wordcloud'"
                :question-data="q"
                mode="result" />

            <q-matrix
                v-if="q.type == 'matrix'"
                :question-data="q"
                mode="result" />

            <q-order
                v-if="q.type == 'order'"
                :question-data="q"
                mode="result" />
        </div>
    </div>
</template>

<style lang="scss">
.question-container {
    .question {
        overflow:auto;
    }
}

.options-list {
    margin-bottom:1em;
    border-left:solid 1px #006487;
}

.option-container {    
    border:none;
    position: relative;
    height:3em;
    line-height:3em;
    overflow:hidden;
    color:white;

    .option-value {        
        background-color:#006487;
        position: absolute;
        top:0;
        left:0;
        height:100%;
        border-bottom-right-radius:8px;    
        border-top-right-radius:8px;

        > span.option-percentage {
            font-weight:bold;
            position: absolute;
            z-index:1;
            right:0;
            margin-right:0.5em;
            padding-left:0.5em;
            padding-right:0.5em;
            background-color:inherit;
        }

        > span.option-title {
            z-index:0;
            position: absolute;
            margin-left:1em;
            margin-right:0.5em;
        }
    }

    + .option-container {
        border-top:none;
        margin-top:0.25em;
    }
}

</style>
  
<script>
import animate from 'animate.css'
import $ from 'jquery'

import qScale from './questionTypes/Scale.vue'
import qMultipleChoice from './questionTypes/MultipleChoice.vue'
import qFreeText from './questionTypes/FreeText.vue'
import qHeading from './questionTypes/Heading.vue'
import qWordCloud from './questionTypes/WordCloud.vue'
import qMatrix from './questionTypes/Matrix.vue'
import qOrder from './questionTypes/Order.vue'

export default {
	name : "ResultDisplay",
	data : function() {
	     return {
	     }
    },
    props : {
        survey : {
            type : Object,
            default : null
        }
    },
	created : function() {
	},	
	methods : {
        surveyClasses(survey) {
            if(survey && survey.design) {
                return "design-" + survey.design
            }
            return ""
        }
	},
	mounted : function() {
	},
	watch : {
	},
	components : {
        qScale,
        qMultipleChoice,
        qFreeText,
        qHeading,
        qWordCloud,
        qMatrix,
        qOrder,
	}
}
</script>
