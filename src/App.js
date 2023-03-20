import React from "react";
import Sidebar from "./components/global/Sidebar";
import Topbar from "./components/global/Topbar";
import Faq from "./components/Faq";
import ParserComponent from "./components/ParserComponent";
import GroupComponent from "./components/GroupComponent";
import { ColorModeContext, useMode } from "./ui/theme";
import { CssBaseline, ThemeProvider } from "@mui/material";
import { Routes, Route } from "react-router-dom";
// import RegisterForm from "./components/RegisterForm";
// import LoginForm from "./components/LoginForm"; 

const App = () => {
  const [theme, colorMode] = useMode();

  // const [isAuth, setIsAuth] = useState(false);

  return (
    <ColorModeContext.Provider value={colorMode}>
      <ThemeProvider theme={theme}>
        <CssBaseline />
        <div className="app">
          <Sidebar />
          <main className="content">
            <Topbar />
            <Routes>
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
              <Route
                path="/parser"
                element={<ParserComponent />}
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