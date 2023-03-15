import React from "react";
import Sidebar from "./components/global/Sidebar";
import Topbar from "./components/global/Topbar";
import Faq from "./components/Faq";
import ParserTable from "./components/ParserTable";
import GroupComponent from "./components/GroupComponent.jsx";
// import RegisterForm from "./components/RegisterForm";
// import LoginForm from "./components/LoginForm";
import { ColorModeContext, useMode } from "./ui/theme";
import { CssBaseline, ThemeProvider } from "@mui/material";
import { Routes, Route } from "react-router-dom";

const App = () => {
  const [theme, colorMode] = useMode();

  // const [isAuth, setIsAuth] = useState(false);

  return (
    <ColorModeContext.Provider value={colorMode}>
      {/* <Route
        path="/login"
        element={<LoginForm setIsAuth={setIsAuth} />}
      />
      <Route
        path="/register"
        element={<RegisterForm setIsAuth={setIsAuth} />}
      />
      <PrivateRoute
        path="/dashboard"
        element={<ParserTable />}
        isAuth={isAuth}
      />*/}
      {/*<PrivateRoute path="faq" element={<Faq />} isAuth={isAuth} />*/}
      <ThemeProvider theme={theme}>
        <CssBaseline />
        <div className="app">
          <Sidebar />
          <main className="content">
            <Topbar />
            <Routes>
              <Route
                path="/dashboard"
                element={<ParserTable />}
              />
              <Route
                path="/faq"
                element={<Faq />}
              />
              <Route
                path="/groups"
                element={<GroupComponent />}
              />
            </Routes>
          </main>
        </div>
      </ThemeProvider>
    </ColorModeContext.Provider>
  );
};

export default App;