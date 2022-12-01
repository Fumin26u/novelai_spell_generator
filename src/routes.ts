import { createRouter, createWebHashHistory } from 'vue-router'
import PromptGenerator from '@/PromptGenerator.vue'
import SavedPrompt from '@/SavedPrompt.vue'
import AccountManage from '@/AccountManager.vue'
const routes = [
    {
        path: '/',
        name: 'generator',
        component: PromptGenerator,
    },
    {
        path: '/saves',
        name: 'saves',
        component: SavedPrompt,
    },
    {
        path: '/login',
        name: 'login',
        component: AccountManage,
    },
    {
        path: '/register',
        name: 'register',
        component: AccountManage,
    },
]

const router = createRouter({
    history: createWebHashHistory(process.env.BASE_URL),
    routes,
})

export default router
