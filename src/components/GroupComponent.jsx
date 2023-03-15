import React, { useState, useEffect } from "react";
import { useMode } from "../ui/theme";
import { Box, TextField, Button } from "@mui/material";
import { tokens } from "../ui/theme";
import axios from "axios";
import Header from "./global/Header";

const GroupSettings = () => {
  const [theme, colorMode] = useMode();
  const colors = tokens(theme.palette.mode);

  const [newGroupName, setNewGroupName] = useState("");
  const [newFilePath, setNewFilePath] = useState("");
  const [groups, setGroups] = useState([]);

  const handleNewGroupNameChange = (event) => {
    setNewGroupName(event.target.value);
  };

  const handleNewFilePathChange = (event) => {
    setNewFilePath(event.target.value);
  };

  const handleCreateGroup = () => {
    axios
    .post("/api/groups/create.php", {
      name: newGroupName,
      filePath: newFilePath,
    })
    .then((response) => {
      setGroups([...groups, response.data]);
      setNewGroupName("");
      setNewFilePath("");
    })
    .catch((error) => console.error(error));
  };

  const handleDeleteGroup = (id) => {
    axios
      .post("/api/groups/delete.php", {
      id: id,
      })
      .then(() => {
      setGroups(groups.filter((group) => group.id !== id));
      })
      .catch((error) => console.error(error));
  };

  const handleListGroups = () => {
    axios
      .get("/api/groups/list.php")
      .then((response) => {
      setGroups(response.data);
      })
      .catch((error) => console.error(error));
  };

  useEffect(() => {
    handleListGroups();
  }, []);

  const handleGroupSettingsDelete = (id) => {
  handleDeleteGroup(id);
  };

  const handleGroupSettingsSubmit = (id, name, filePath) => {
    axios
    .post("/api/groups/update.php", {
      id: id,
      name: name,
      filePath: filePath,
    })
    .then(() => {
      setGroups(
      groups.map((group) => {
        if (group.id === id) {
          return {
            id: group.id,
            name: name,
            filePath: filePath,
          };
        }
        return group;
      })
      );
    })
    .catch((error) => console.error(error));
    };

  const handleGroupSettingsCancel = () => {
    handleListGroups();
  };

  const handleGroupSettingsOpen = (id) => {
  // ...
  };

return (
  <>
    <Box m="20px">
    <Header title="Group Settings" subtitle="Manage your groups" />
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
            backgroundColor: colors.blueAccent[900],
            borderRadius: "4px",
            padding: "10px",
            height: "calc(100% - 10px)",
          },
          "& .MuiDataGrid-cell--withRenderer": {
            paddingLeft: "30px",
            paddingRight: "30px",
          },
        }}
      >
        <DataGrid
          rows={groups}
          columns={columns}
          pageSize={10}
          autoHeight={true}
          disableSelectionOnClick={true}
          disableColumnMenu={true}
          disableColumnFilter={true}
          disableColumnSelector={true}
          disableDensitySelector={true}
          getRowClassName={(params) =>
            `${params.row.id % 2 === 0 ? "even-row" : "odd-row"} ${params.row.id === editingGroupId ? "editing-row" : ""}`
          }
          components={{
          Toolbar: CustomToolbar,
          }}
          onEditCellChangeCommitted={(params) => {
          handleGroupSettingsSubmit(
          params.id,
          params.field === "name" ? params.props.value : params.row.name,
          params.field === "filePath" ? params.props.value : params.row.filePath
          );
          setEditingGroupId(null);
          }}
        />
        <GroupSettingsDialog
          open={editingGroupId !== null}
          onSubmit={handleGroupSettingsSubmit}
          onCancel={handleGroupSettingsCancel}
          group={groups.find((group) => group.id === editingGroupId)}
        />
        </Box>
        <Box m="40px 0">
          <TextField
          label="New Group Name"
          variant="outlined"
          value={newGroupName}
          onChange={handleNewGroupNameChange}
        />
        <Box m="20px 0" />
          <TextField
            label="New File Path"
            variant="outlined"
            value={newFilePath}
            onChange={handleNewFilePathChange}
        />
          <Box m="20px 0" />
            <Button variant="contained" onClick={handleCreateGroup}>
              Create Group
          </Button>
        </Box>
      </Box>
    </>
  );
};

export default GroupSettings;