import React, { useState, useEffect } from "react";
import axios from "axios";

const AdminReportPage = () => {
  const [logs, setLogs] = useState([]);

  useEffect(() => {
    // Загрузка журнала аутентификации из сервера
    axios.get("/api/logs").then((response) => {
      setLogs(response.data);
    });
  }, []);

  return (
    <div>
      <h1>Admin Report</h1>
      <table>
        <thead>
          <tr>
            <th>User</th>
            <th>Action</th>
            <th>Time</th>
          </tr>
        </thead>
        <tbody>
          {logs.map((log) => (
            <tr key={log.id}>
              <td>{log.user}</td>
              <td>{log.action}</td>
              <td>{new Date(log.time).toLocaleString()}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};

export default AdminPage;
