import React, { useState } from 'react';
import {
  IconButton,
  Modal,
  Button,
  Box,
} from '@mui/material';
import { Logout } from '@mui/icons-material';

const LogoutModal = ({ open, handleClose, handleLogout }) => (
  <Modal
    open={open}
    onClose={handleClose}
    aria-labelledby="logout-modal-title"
    aria-describedby="logout-modal-description"
  >
    <Box
      sx={{
        position: 'absolute',
        top: '50%',
        left: '50%',
        transform: 'translate(-50%, -50%)',
        bgcolor: 'background.paper',
        boxShadow: 24,
        p: 4,
        borderRadius: '4px',
      }}
    >
      <h2 id="logout-modal-title">Are you sure you want logout?</h2>
      <Box sx={{ display: 'flex', justifyContent: 'flex-end', mt: 2 }}>
        <Button onClick={handleClose} color="primary" variant="contained">
          Отмена
        </Button>
        <Button onClick={handleLogout} color="error" variant="contained" sx={{ ml: 2 }}>
          Да
        </Button>
      </Box>
    </Box>
  </Modal>
);

const LogoutButton = () => {
  const [isModalOpen, setIsModalOpen] = useState(false);

  const handleModalOpen = () => {
    setIsModalOpen(true);
  };

  const handleModalClose = () => {
    setIsModalOpen(false);
  };

  const handleLogout = () => {
    // Logout code from Login Register Comp
    setIsModalOpen(false);
  };

  return (
    <>
      <IconButton onClick={handleModalOpen}>
        <Logout />
      </IconButton>
      <LogoutModal open={isModalOpen} handleClose={handleModalClose} handleLogout={handleLogout} />
    </>
  );
};
