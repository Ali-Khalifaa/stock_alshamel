import guest from "../../middleware/guest";

export default [
    {
        path: '/login',
        name: 'login',
        component: () => import('../../views/pages/auth/login'),
        meta: {
            middleware: [guest]
        },
    },
]
