import React, { useState, useEffect } from "react";
import axios from "axios";
import {
  Grid,
  Card,
  CardHeader,
  CardContent,
  List,
  ListItem,
  ListItemText,
  ListItemSecondaryAction,
  IconButton,
  TextField,
  Button,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Typography,
  Box
} from "@mui/material";
import { tokens } from "../ui/theme";
import {
  Add as AddIcon,
  Delete as DeleteIcon,
  Edit as EditIcon,
} from "@mui/icons-material";
import { DataGrid } from "@mui/x-data-grid";
import Header from "./global/Header";
import { useTheme } from '@mui/material';

const API_BASE_URL = "http://localhost/";

function FileList() {
  const [files, setFiles] = useState([]);
  const [groups, setGroups] = useState([]);
  const [selectedGroup, setSelectedGroup] = useState("");
  const [newGroupName, setNewGroupName] = useState("");
  const [createGroupDialogOpen, setCreateGroupDialogOpen] = useState(false);
  const [editGroupDialogOpen, setEditGroupDialogOpen] = useState(false);
  const [selectedGroupId, setSelectedGroupId] = useState("");
  const theme = useTheme();
  const colors = tokens(theme.palette.mode);

  // Fetch files and groups on component mount
  useEffect(() => {
    fetchFiles();
    fetchGroups();
  }, []);

  // Fetch files from server
  const fetchFiles = () => {
    axios
      .get(API_BASE_URL + "api/groups/list_files.php")
      .then((response) => setFiles(response.data))
      .catch((error) => console.log(error));
  };

  // Fetch groups from server
  const fetchGroups = () => {
    axios
      .get(API_BASE_URL + "api/groups/list_groups.php")
      .then((response) => setGroups(response.data))
      .catch((error) => console.log(error));
  };

  // Handle file adding to a group
  const handleAddFileToGroup = (fileName) => {
    axios
      .post(API_BASE_URL + "api/groups/add_file_to_group.php", {
        group: selectedGroup,
        file: fileName,
      })
      .then((response) => fetchFiles())
      .catch((error) => console.log(error));
  };

  // Handle group creation
  const handleCreateGroup = () => {
    axios
      .post(API_BASE_URL + "api/groups/create_group.php", {
        name: newGroupName,
      })
      .then((response) => {
        fetchGroups();
        setCreateGroupDialogOpen(false);
        setNewGroupName("");
      })
      .catch((error) => console.log(error));
  };

  // Handle group deletion
  const handleDeleteGroup = (groupId) => {
    axios
      .post(API_BASE_URL + "api/groups/delete_group.php", {
        id: groupId,
      })
      .then((response) => fetchGroups())
      .catch((error) => console.log(error));
  };

  // Handle group name edit
  const handleEditGroup = () => {
    axios
      .post(API_BASE_URL + "api/groups/update_group.php", {
        id: selectedGroupId,
        name: newGroupName,
      })
      .then((response) => {
        fetchGroups();
        setEditGroupDialogOpen(false);
        setSelectedGroupId("");
        setNewGroupName("");
      })
      .catch((error) => console.log(error));
  };

  const columns = [
    { field: "name", headerName: "Name", width: 250 },
    { field: "date_created", headerName: "Date Created", width: 250 },
    { field: "group", headerName: "Group", width: 250 },
    {
      field: "actions",
      headerName: "Actions",
      sortable: false,
      width: 250,
      renderCell: (params) => (
        <>
          {!params.row ? (
            <>
              <IconButton onClick={() => handleAddFileToGroup(params.value)}>
                <AddIcon />
              </IconButton>
              <Dialog
                open={createGroupDialogOpen}
                onClose={() => setCreateGroupDialogOpen(false)}
              >
                <DialogTitle>Create Group</DialogTitle>
                <DialogContent>
                  <TextField
                    label="Group Name"
                    value={newGroupName}
                    onChange={(e) => setNewGroupName(e.target.value)}
                  />
                </DialogContent>
                <DialogActions>
                  <Button onClick={() => setCreateGroupDialogOpen(false)}>
                    Cancel
                  </Button>
                  <Button onClick={handleCreateGroup} color="primary">
                    Create
                  </Button>
                </DialogActions>
              </Dialog>
              <IconButton
                onClick={() => {
                  setSelectedGroupId(params.row.id);
                  setNewGroupName(params.row.name);
                  setEditGroupDialogOpen(true);
                }}
              >
                <EditIcon />
              </IconButton>
              <Dialog
                open={editGroupDialogOpen}
                onClose={() => setEditGroupDialogOpen(false)}
              >
                <DialogTitle>Edit Group Name</DialogTitle>
                <DialogContent>
                  <TextField
                    label="Group Name"
                    value={newGroupName}
                    onChange={(e) => setNewGroupName(e.target.value)}
                  />
                </DialogContent>
                <DialogActions>
                  <Button onClick={() => setEditGroupDialogOpen(false)}>
                    Cancel
                  </Button>
                  <Button onClick={handleEditGroup} color="primary">
                    Save
                  </Button>
                </DialogActions>
              </Dialog>
              <IconButton onClick={() => handleDeleteGroup(params.row.id)}>
                <DeleteIcon />
              </IconButton>
            </>
          ) : null}
        </>
      ),
    },
  ];

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
        <Grid container spacing={2}>
          <Grid item xs={12} md={6}>
            <Card>
              <CardHeader title="Files" />
              <CardContent>
                <List>
                  {files.map((file, index) => (
                    <ListItem key={index}>
                      <ListItemText primary={file.name} />
                      <ListItemSecondaryAction>
                        <IconButton
                          onClick={() => handleAddFileToGroup(file.name)}
                        >
                          <AddIcon />
                        </IconButton>
                      </ListItemSecondaryAction>
                    </ListItem>
                  ))}
                </List>
              </CardContent>
            </Card>
          </Grid>
          <Grid item xs={12} md={6}>
            <Card>
              <CardHeader
                title="Groups"
                action={
                  <IconButton onClick={() => setCreateGroupDialogOpen(true)}>
                    <AddIcon />
                  </IconButton>
                }
              />
              <CardContent>
                <DataGrid
                  rows={groups}
                  columns={columns}
                  pageSize={10}
                  disableSelectionOnClick
                  onRowClick={(params) => setSelectedGroup(params.row.name)}
                  getRowId={(row) => row.name}
                />
              </CardContent>
            </Card>
          </Grid>
          <Dialog
            open={selectedGroup !== ""}
            onClose={() => setSelectedGroup("")}
          >
            <DialogTitle>{selectedGroup}</DialogTitle>
            <DialogContent>
              <Typography variant="h6">Files</Typography>
              <List>
                {files
                  .filter((file) => file.group === selectedGroup)
                  .map((file, index) => (
                    <ListItem key={index}>
                      <ListItemText primary={file.name} />
                    </ListItem>
                  ))}
              </List>
            </DialogContent>
            <DialogActions>
              <Button onClick={() => setSelectedGroup("")} color="primary">
                Close
              </Button>
            </DialogActions>
          </Dialog>
        </Grid>
      </Box>
    </Box>
  );
}

export default FileList;