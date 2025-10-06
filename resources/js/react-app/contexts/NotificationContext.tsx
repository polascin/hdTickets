import React, { createContext, useContext } from 'react';
const NotificationContext = createContext({});
export const NotificationProvider = ({ children }: { children: React.ReactNode }) => <div>{children}</div>;
export const useNotification = () => useContext(NotificationContext);
