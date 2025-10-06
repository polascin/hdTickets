import React, { createContext, useContext } from 'react';
const AuthContext = createContext({});
export const AuthProvider = ({ children }: { children: React.ReactNode }) => <div>{children}</div>;
export const useAuth = () => useContext(AuthContext);
