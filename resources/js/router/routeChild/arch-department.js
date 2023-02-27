import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: "/arch-departments",
        name: "arch-departments",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../../views/pages/arch-department/index"),
    },
];
