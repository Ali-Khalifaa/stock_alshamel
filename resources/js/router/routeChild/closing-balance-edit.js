import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: "/closing-balance-edit",
        name: "closing-balance-edit",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../../views/pages/closing-balance/editform"),
    },
];
