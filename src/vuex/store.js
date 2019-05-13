import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex);
Vue.config.debug = false;

const state = {
	appTitle : "InstaGauge",
	appLayout : "newton",
	user : null,
	runningId : 0,
	whatsNewStatus : null,
};

const mutations = { 
	setAppLayout(state, newAppLayout) {
		state.appLayout = newAppLayout;
	},
	setAppTitle(state, newAppTitle) {
		state.appTitle = newAppTitle;
	},
	setUser(state, newUser) {
		state.user = newUser;
	},
	setRunningId(state, newId) {
		state.runningId = newId;
	},
	setWhatsNewStatus(state, o) {
		state.whatsNewStatus = o;
	},
};

export default new Vuex.Store({
	state,
	mutations,
	actions : {
		setAppLayout : ({ commit }, m) => {
			commit("setAppLayout", m);
		},
		setAppTitle : ({ commit }, m) => {
			commit("setAppTitle", m);
		},
		setUser : ({ commit }, m) => {
			commit("setUser", m);
		},
		setRunningId : ({ commit }, m) => {
			commit("setRunningId", m);
		},
		setWhatsNewStatus : ({ commit }, m) => {
			commit("setWhatsNewStatus", m);
		},
	},
	getters : {
		appLayout : (state) => { return state.appLayout; },
		appTitle : (state) => { return state.appTitle; },
		user : (state) => { return state.user; },
		runningId : (state) => { return state.runningId; },
		whatsNewStatus : (state) => { return state.whatsNewStatus; },
	}
});
