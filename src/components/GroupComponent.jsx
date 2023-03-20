import { useState } from "react";
import { v4 as uuidv4 } from "uuid";
import { Edit, Download } from "@mui/icons-material";
import {
  Button,
  Container,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  FormControl,
  FormHelperText,
  IconButton,
  InputLabel,
  List,
  Checkbox,
  ListItem,
  ListItemSecondaryAction,
  ListItemText,
  MenuItem,
  Select,
  Snackbar,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableRow,
  TextField,
  Typography,
  useTheme,
} from "@mui/material";
import { Add, Close, Delete } from "@mui/icons-material";

const GroupComponent = () => {
  const [groups, setGroups] = useState([
    {
      id: uuidv4(),
      name: "Group 1",
      description: "This is group 1",
      files: [
        {
          id: uuidv4(),
          name: "File 1",
          size: "10 MB",
        },
        {
          id: uuidv4(),
          name: "File 2",
          size: "5 MB",
        },
      ],
    },
    {
      id: uuidv4(),
      name: "Group 2",
      description: "This is group 2",
      files: [
        {
          id: uuidv4(),
          name: "File 3",
          size: "2 MB",
        },
        {
          id: uuidv4(),
          name: "File 4",
          size: "8 MB",
        },
      ],
    },
  ]);

  const [showModal, setShowModal] = useState(false);
  const [newGroup, setNewGroup] = useState({
    name: "",
    description: "",
    files: [],
  });

  const handleAddFile = (groupId) => {
    const file = {
      id: uuidv4(),
      name: `New File ${Math.floor(Math.random() * 100)}`,
      size: `${Math.floor(Math.random() * 10) + 1} MB`,
    };

    const updatedGroups = groups.map((group) => {
      if (group.id === groupId) {
        return {
          ...group,
          files: [...group.files, file],
        };
      }
      return group;
    });

    setGroups(updatedGroups);
  };

  const handleInputChange = (event) => {
    setNewGroup({
      ...newGroup,
      [event.target.name]: event.target.value,
    });
  };

  const handleSubmit = (event) => {
    event.preventDefault();
    const newGroupWithId = { ...newGroup, id: uuidv4() };
    setGroups([...groups, newGroupWithId]);
    setNewGroup({ name: "", description: "", files: [] });
    setShowModal(false);
  };

  return (
    <Container sx={{ mt: 4 }}>
      <Typography variant="h4" sx={{ mb: 2 }}>
        Groups
      </Typography>
      {/* Group list */}
      <Table>
        <TableHead>
          <TableRow>
            <TableCell>Name</TableCell>
            <TableCell>Description</TableCell>
            <TableCell>Files</TableCell>
            <TableCell>Actions</TableCell>
          </TableRow>
        </TableHead>

        <TableBody>
          {groups.map((group) => (
            <TableRow key={group.id}>
              <TableCell>{group.name}</TableCell>
              <TableCell>{group.description}</TableCell>
              <TableCell>
                <List>
                  {group.files.map((file) => (
                    <ListItem key={file.id}>
                      <ListItemText primary={file.name} secondary={file.size} />
                      <ListItemSecondaryAction>
                        <IconButton onClick={() => alert("Download")}>
                          <Download />
                        </IconButton>
                        <IconButton onClick={() => alert("Delete")}>
                          <Delete />
                        </IconButton>
                      </ListItemSecondaryAction>
                    </ListItem>
                  ))}
                </List>

                <Button
                  variant="outlined"
                  size="small"
                  startIcon={<Add />}
                  onClick={() => handleAddFile(group.id)}
                >
                  Add file
                </Button>
              </TableCell>
              <TableCell>
                <IconButton onClick={() => alert("Edit")}>
                  <Edit />
                </IconButton>
                <IconButton onClick={() => alert("Delete")}>
                  <Delete />
                </IconButton>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>

      {/* Add group button */}
      <Button
        variant="contained"
        sx={{ mt: 4 }}
        startIcon={<Add />}
        onClick={() => setShowModal(true)}
      >
        Add Group
      </Button>

      {/* Add group modal */}
      <Dialog open={showModal} onClose={() => setShowModal(false)}>
        <DialogTitle>Add Group</DialogTitle>
        <DialogContent>
          <form onSubmit={handleSubmit}>
            <TextField
              required
              fullWidth
              margin="normal"
              label="Name"
              name="name"
              value={newGroup.name}
              onChange={handleInputChange}
            />
            <TextField
              required
              fullWidth
              margin="normal"
              label="Description"
              name="description"
              value={newGroup.description}
              onChange={handleInputChange}
            />
            <FormControl fullWidth margin="normal">
              <InputLabel id="files-select-label">Files</InputLabel>
              <Select
                labelId="files-select-label"
                id="files-select"
                multiple
                value={newGroup.files}
                onChange={(event) =>
                  setNewGroup({ ...newGroup, files: event.target.value })
                }
                input={<TextField />}
                renderValue={(selected) =>
                  selected.map((file) => file.name).join(", ")
                }
              >
                {Array.from({ length: 10 }).map((_, index) => (
                  <MenuItem
                    key={index}
                    value={{
                      id: uuidv4(),
                      name: `File ${index + 1}`,
                      size: "1 MB",
                    }}
                  >
                    <Checkbox
                      checked={newGroup.files.some(
                        (file) => file.id === index + 1
                      )}
                    />
                    <ListItemText primary={`File ${index + 1}`} />
                  </MenuItem>
                ))}
              </Select>
              <FormHelperText>
                Select files to include in the group
              </FormHelperText>
            </FormControl>
            <DialogActions>
              <Button onClick={() => setShowModal(false)}>Cancel</Button>
              <Button type="submit">Add</Button>
            </DialogActions>
          </form>
        </DialogContent>
      </Dialog>
    </Container>
  );
};

export default GroupComponent;