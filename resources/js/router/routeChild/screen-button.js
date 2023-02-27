import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: '/screen-button',
        name: 'screen-button',
        meta: {
            middleware: [auth,checkAuth]
        },
        component: () => import('../../views/pages/screen-button/index'),
    },
];
