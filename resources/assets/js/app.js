
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
import Vue from 'vue'
import VueRouter from 'vue-router'
//import VueAutosuggest from "vue-autosuggest";

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.use(VueRouter);
import PriceInputIndex from './components/priceinput/PriceInputIndex.vue';
import PriceInputCreate from './components/priceinput/priceInputCreate.vue';
import PriceInputEdit from './components/priceinput/priceInputEdit.vue';

const routes = [
    {
        path: '/',
        component: PriceInputIndex, 
        name: 'priceInputIndex'
    },
    {
        path: '/create', 
        component: PriceInputCreate, 
        name: 'createVM'
    },
    {
        path: '/edit/:id', 
        component: PriceInputEdit, 
        name: 'editVM'
    }
]

const router = new VueRouter({ routes });
const app = new Vue({
    el: '#app',
    router
});
//const app = new Vue({ router }).$mount('#app')

