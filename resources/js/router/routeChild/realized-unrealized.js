import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: "/realized-unrealized",
        name: "realized-unrealized",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../../views/pages/realized-unrealized/index"),
    },
];
