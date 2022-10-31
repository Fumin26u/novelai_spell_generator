import { createRouter, createWebHashHistory } from 'vue-router'
import App from './App.vue'
import TestComponent from './TestComponent.vue'
const routes = [
    {path: '/App', component: App},
    {path: '/TestComponent', component: TestComponent},
]

const router = createRouter({
    history: createWebHashHistory(),
    routes,
})

export default router


