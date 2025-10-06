import React from 'react';
const Modal: React.FC<any> = ({ children, isOpen, onClose }) => {
  if (!isOpen) return null;
  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div className="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <button onClick={onClose} className="float-right text-gray-400 hover:text-gray-600">×</button>
        {children}
      </div>
    </div>
  );
};
export default Modal;