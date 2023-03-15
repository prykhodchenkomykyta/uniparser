import React, { useState } from "react";
import { useMode, tokens } from "../ui/theme";
import { Box } from "@mui/material";
import { CSVLink } from "react-csv";
import {
  Button,
  CircularProgress,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper,
  FormControl,
  Input,
  InputLabel,
} from "@mui/material";
import axios from "axios";
import Header from "./global/Header";
import { saveAs } from "file-saver";

const ParserTable = () => {
  const [theme, colorMode] = useMode();
  const colors = tokens(theme.palette.mode);

  const [data, setData] = useState(storedData || []);
  const [file, setFile] = useState(null);

  const handleFileChange = (event) => {
    setFile(event.target.files[0]);
  };

  const handleUploadClick = () => {
    const formData = new FormData();
    formData.append("csvFile", file);

    axios
      .post("/api/parser/index.php", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      })
      .then((response) => {
        setData(response.data);
        localStorage.setItem("csvData", JSON.stringify(response.data));
      })
      .catch((error) => console.error(error));
  };

  const handleClearStorage = () => {
    localStorage.removeItem("csvData");
    setData([]);
  };

  const handleExportClick = () => {
    const csvData = data.map((row) => row.join(",")).join("\n");
    const blob = new Blob([csvData], { type: "text/csv;charset=utf-8;" });
    saveAs(blob, "data.csv");
  };

  const storedData = JSON.parse(localStorage.getItem("csvData"));

  return (
    <Box m="20px">
      <Header title="CSV" subtitle="List of data from CSV" />
      <Box
        m="40px 0 0 0"
        height="70vh"
        sx={{
          "& .MuiDataGrid-root": {
            border: "none",
          },
          "& .MuiDataGrid-cell": {
            borderBottom: "none",
          },
          "& .name-column--cell": {
            color: colors.greenAccent[300],
          },
          "& .MuiDataGrid-columnHeaders": {
            backgroundColor: colors.blueAccent[700],
            borderBottom: "none",
          },
          "& .MuiDataGrid-virtualScroller": {
            backgroundColor: colors.primary[400],
          },
          "& .MuiDataGrid-footerContainer": {
            borderTop: "none",
            backgroundColor: colors.blueAccent[700],
          },
          "& .MuiDataGrid-toolbarContainer .MuiButton-text": {
            color: `${colors.grey[100]} !important`,
          },
        }}
      >
        <FormControl>
          <InputLabel htmlFor="csv-file-input">Choose CSV file</InputLabel>
          <Input
            id="csv-file-input"
            type="file"
            accept=".csv"
            onChange={handleFileChange}
          />
        </FormControl>
        <Button
          variant="contained"
          color="primary"
          disabled={!file}
          onClick={handleUploadClick}
        >
          Upload
        </Button>
        <Button variant="contained" onClick={handleClearStorage}>
          Refresh
        </Button>
        <Button variant="contained" onClick={handleExportClick}>
          Export in CSV
        </Button>
        <TableContainer component={Paper}>
          <Table>
            <TableHead>
              <TableRow>
                {data[0].map((header) => (
                  <TableCell key={header}>{header}</TableCell>
                ))}
              </TableRow>
            </TableHead>
            <TableBody>
              {data.slice(1).map((row, index) => (
                <TableRow key={index}>
                  {row.map((cell) => (
                    <TableCell key={cell}>{cell}</TableCell>
                  ))}
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </TableContainer>
      </Box>
    </Box>
  );
};
export default ParserTable;