import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: "/closing-balance",
        name: "closing-balance",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../../views/pages/closing-balance/index"),
    },
];
