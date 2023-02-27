import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: "/openning-balance",
        name: "openning-balance",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../../views/pages/openning-balance/index"),
    },
];
