import React from "react";
import { Route, Navigate } from "react-router-dom";

function PrivateRoute({ component: Component, isAuth, ...props }) {
  return (
    <Route
      {...props}
      element={isAuth ? <Component {...props} /> : <Navigate to="/login" />}
    />
  );
}

export default PrivateRoute;