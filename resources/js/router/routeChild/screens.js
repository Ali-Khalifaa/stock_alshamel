import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: '/screens',
        name: 'screens',
        meta: {
            middleware: [auth,checkAuth]
        },
        component: () => import('../../views/pages/screens/index'),
    },
];
