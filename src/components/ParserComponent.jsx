import React, { useState, useEffect } from "react";
import { tokens } from "../ui/theme";
import { Box } from "@mui/material";
import CloudUploadIcon from "@mui/icons-material/CloudUpload";
import DeleteIcon from "@mui/icons-material/Delete";
import EditIcon from "@mui/icons-material/Edit";
import {
  DataGrid,
  GridToolbarContainer,
  GridToolbarColumnsButton,
  GridToolbarDensitySelector,
  GridToolbarExport,
  GridToolbarFilterButton,
} from "@mui/x-data-grid";
import { useTheme } from "@mui/material";
import axios from "axios";
import Header from "./global/Header";
import { saveAs } from "file-saver";
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogContentText,
  DialogActions,
  Button,
  TextField,
  FormControl,
  Input,
} from "@mui/material";

const ParserComponent = () => {
  const theme = useTheme();
  const colors = tokens(theme.palette.mode);

  const [data, setData] = useState([]);
  const [filterModel, setFilterModel] = useState({ items: [] });
  const [selectedRow, setSelectedRow] = useState(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingRow, setEditingRow] = useState(null);

  useEffect(() => {
    const savedData = localStorage.getItem("parserData");
    if (savedData) {
      setData(JSON.parse(savedData));
    }
  }, []);

  useEffect(() => {
    localStorage.setItem("parserData", JSON.stringify(data));
  }, [data]);

  const handleRowClick = (params, event) => {
    setSelectedRow(params.row);
  };

  const handleFileUpload = (event) => {
    const file = event.target.files[0];
    const formData = new FormData();
    formData.append("file", file);
    axios
      .post("http://localhost/parser/src/api/parser/index.php", formData)
      .then((response) => {
        setData([...data, ...response.data]);
      });
  };

  const handleExportClick = () => {
    const csvData = data.map((row) => row.join(",")).join("\n");
    const blob = new Blob([csvData], { type: "text/csv;charset=utf-8" });
    saveAs(blob, "data.csv");
  };

  const handleFilterModelChange = (model) => {
    setFilterModel(model);
  };

  const handleDeleteRow = (id) => {
    setData(data.filter((row) => row.id !== id));
  };

  const handleEditRow = (row) => {
    setEditingRow(row);
    setIsModalOpen(true);
  };

  const handleModalClose = () => {
    setIsModalOpen(false);
    setEditingRow(null);
  };

  const handleModalSave = () => {
    const newData = data.map((row) =>
      row.id === editingRow.id ? editingRow : row
    );
    setData(newData);
    handleModalClose();
  };

  const handleClearStorage = () => {
    localStorage.clear();
    setData([]);
  };

  const columns = data[0] ? Object.keys(data[0]).map((key) => ({
field: key,
headerName: key,
headerClassName: "grid-header",
sortable: true,
width: 200,
filterable: true,
filterOperators: ["contains", "equals", "startsWith", "endsWith"],
filterValue: filterModel.items.find((item) => item.columnField === key)
?.value,
renderCell: (params) => (
<Box
sx={{
display: "flex",
alignItems: "center",
justifyContent: "center",
height: "100%",
width: "100%",
color: params.row.color,
}}
>
{params.value}
</Box>
),
})) : [];

const rows = data.map((row) => ({ ...row, id: row.id.toString() }));

return (
<>
<Header />
<Box
sx={{
display: "flex",
flexDirection: "column",
height: "calc(100vh - 64px)",
}}
>
<Box
sx={{
display: "flex",
alignItems: "center",
justifyContent: "space-between",
padding: "0 24px",
backgroundColor: colors.grey[900],
color: colors.grey[50],
}}
>
<Box
sx={{
display: "flex",
alignItems: "center",
}}
>
<label htmlFor="file-upload">
<input
id="file-upload"
type="file"
style={{ display: "none" }}
onChange={handleFileUpload}
/>
<Button
variant="outlined"
component="span"
startIcon={<CloudUploadIcon />}
>
Upload File
</Button>
</label>
<Button
variant="outlined"
startIcon={<DeleteIcon />}
onClick={handleClearStorage}
>
Clear Storage
</Button>
</Box>
<Box
sx={{
display: "flex",
alignItems: "center",
}}
>
<Button
variant="outlined"
startIcon={<EditIcon />}
onClick={() => handleEditRow(selectedRow)}
disabled={!selectedRow}
>
Edit Row
</Button>
<Button
           variant="outlined"
           onClick={handleExportClick}
           disabled={!data.length}
         >
Export CSV
</Button>
</Box>
</Box>
<Box sx={{ flexGrow: 1 }}>
<DataGrid
columns={columns}
rows={rows}
onRowClick={handleRowClick}
filterModel={filterModel}
onFilterModelChange={handleFilterModelChange}
components={{
Toolbar: CustomToolbar,
}}
/>
</Box>
</Box>
<EditRowModal
     isOpen={isModalOpen}
     handleClose={handleModalClose}
     handleSave={handleModalSave}
     rowData={editingRow}
     setRowData={setEditingRow}
   />
</>
);
};

const CustomToolbar = () => {
return (
<GridToolbarContainer>
<GridToolbarColumnsButton />
<GridToolbarFilterButton />
<GridToolbarDensitySelector />
<GridToolbarExport />
</GridToolbarContainer>
);
};

const EditRowModal = ({
isOpen,
handleClose,
handleSave,
rowData,
setRowData,
}) => {
const handleChange = (event) => {
const { name, value } = event.target;
setRowData({ ...rowData, [name]: value });
};

return (
<Dialog open={isOpen} onClose={handleClose}>
<DialogTitle>Edit Row</DialogTitle>
<DialogContent>
<DialogContentText>
Please edit the row values below:
</DialogContentText>
<TextField
name="name"
label="Name"
value={rowData.name}
onChange={handleChange}
margin="normal"
/>
<TextField
name="age"
label="Age"
type="number"
value={rowData.age}
onChange={handleChange}
margin="normal"
/>
<TextField
name="email"
label="Email"
value={rowData.email}
onChange={handleChange}
margin="normal"
/>
</DialogContent>
<DialogActions>
<Button onClick={handleClose}>Cancel</Button>
<Button onClick={handleSave} color="primary">
Save
</Button>
</DialogActions>

</Dialog>
);
};
export default ParserComponent;