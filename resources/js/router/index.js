import {createWebHistory, createRouter} from 'vue-router';
import store from '../store';

/* Guest Component */
const Login = () => import('../components/Login.vue');
const Register = () => import('../components/Register.vue');
/* Guest Component */

/* Layouts */
const DefaultLayout = () => import('../components/layouts/Default.vue');
/* Layouts */

/* Authenticated Component */
const Dashboard = () => import('../components/Dashboard.vue');
/* Authenticated Component */

/* User Components */
const Projects = () => import('../components/Projects.vue');
const Project = () => import('../components/Project.vue');
const Resources = () => import("../components/Resources.vue");
const Holidays = () => import("../components/Holidays.vue");
const Settings = () => import("../components/Settings.vue");
/* User Components */

const routes = [
    {
        name: "login",
        path: "/login",
        component: Login,
        meta: {
            middleware: "guest",
            title: 'Login'
        }
    },
    {
        name: "register",
        path: "/register",
        component: Register,
        meta: {
            middleware: "guest",
            title: 'Register'
        }
    },
    {
        path: "/",
        component: DefaultLayout,
        meta: {
            middleware: "auth"
        },
        children: [
            {
                name: "dashboard",
                path: '/',
                component: Dashboard,
                meta: {
                    title: 'Dashboard'
                }
            },
            {
                name: "projects",
                path: '/projects',
                component: Projects,
                meta: {
                    title: 'Projects'
                }
            },
            {
                name: "project",
                path: '/project/:identifier',
                component: Project,
                meta: {
                    title: 'Project'
                }
            },
            {
                name: "resources",
                path: '/resources',
                component: Resources,
                meta: {
                    title: 'Resources'
                }
            },
            {
                name: "holidays",
                path: '/holidays',
                component: Holidays,
                meta: {
                    title: 'Holidays'
                }
            },
            {
                name: "settings",
                path: '/settings',
                component: Settings,
                meta: {
                    title: 'Settings'
                }
            },
        ]
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes, // short for 'routes: routes'
})

router.beforeEach((to, from, next) => {
    document.title = to.meta.title
    if (to.meta.middleware === "guest") {
        if (store.state.auth.authenticated) {
            next({ name: "dashboard" })
        }
        next()
    } else {
        if (store.state.auth.authenticated) {
            next()
        } else {
            next({ name: "login" })
        }
    }
})

export default router;
