<template>
	<main>
		<section>
			<div class="container">
				<div class="row">
					<div class="col-md-12">
                        <h1>Admin</h1>
                        <h2>Overview</h2>
                    </div>
                    
					<div class="col-md-12" v-if="surveys">
                        <p>Your surveys:</p>
                        
                        <p v-if="surveys.length == 0">
                            No surveys yet.
                        </p>

                        <ol class="survey-list" reversed>
                            <li v-for="s in surveys" :key="s._key">
                                <div class="btn-group" role="group">
                                    <button 
                                        class="btn" 
                                        :title="s.votingDisabled ? 'disabled' : 'enabled'" 
                                        @click.stop="toggleVoting(s)"
                                        :class="{ 'btn-success' : !s.votingDisabled, 'btn-warning' : s.votingDisabled }"
                                        :disabled="toggling">
                                        <font-awesome-icon icon="toggle-on" v-if="(s.votingDisabled == undefined) || (s.votingDisabled == false)" />
                                        <font-awesome-icon icon="toggle-off" v-if="s.votingDisabled == true" />
                                    </button>
                                    <router-link 
                                        title="details..."
                                        class="btn btn-info"
                                        :to="{ name : 'Details', params : { id : s._key, pw : s.password } }">
                                        <font-awesome-icon icon="search" />
                                    </router-link>
                                    <router-link 
                                        v-if="s.status == 'draft'"
                                        title="edit..."
                                        class="btn btn-primary"
                                        :to="{ name : 'AdminSurveyEdit', params : { id : s._key } }">
                                        <font-awesome-icon icon="edit" />
                                    </router-link>
                                </div>

                                <button
                                    v-if="s.status == 'draft'"
                                    title="delete!"
                                    @click.stop="deleteSurvey(s)"
                                    class="btn btn-secondary delete-button">
                                    <font-awesome-icon icon="trash-alt" />
                                </button>

                                <router-link :to="{ name : 'Survey', params : { id : s._key } }">
                                    {{ s.title ? s.title : moment(s.createdAt, "x").format("YYYY-MM-DD HH:mm") }}
                                </router-link>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
    </main>
</template>

<style lang="scss" scoped>
.survey-list {
    li {
        padding-top:0.25rem;
        padding-bottom:0.25rem;
    }

    .delete-button {
        float:right;
    }

    li:hover {
        background-color:#ddd;
    }

    li + li {
        //margin-top:0.5rem;
    }
}
</style>

<script>
import moment from 'moment'

export default {
    name : "AdminStart",
    data : function() {
        return {
            moment : moment,
            surveys : null,
            toggling : false
        };
    },
    created : function() {
        var me = this;

        me.$http.get("surveys/mine").then((d) => {
            d = d.data;
            if(d.ok) {
                me.surveys = d.items;
            } else {
                alert(d.msg);
            }
        });
    },
    methods : {
        toggleVoting(s) {
            var me = this;
            if(me.toggling) return;
            me.toggling = true;
            me.$http.post("surveys/" + s._key + "/toggleVoting", {}, { headers : { "X-SURVEY-PW" : s.password }}).then((d) => {
                d = d.data;
                if(d.ok) {
                    s.votingDisabled = d.survey.votingDisabled;
                }
                me.toggling = false;
            });            
        },
        deleteSurvey(s) {
            var me = this;

            me.$http.delete("admin/surveys/" + s._key + "/details", { headers : { "X-SURVEY-PW" : s.password }}).then((d) => {
                d = d.data;
                if(d.ok) {
                    me.surveys = d.items;
                } else {
                    alert(d.msg);
                }
            });
        }
    },
    components : {
    }
}
</script>