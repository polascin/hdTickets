import React, { createContext, useContext } from 'react';
const ThemeContext = createContext({});
export const ThemeProvider = ({ children }: { children: React.ReactNode }) => <div>{children}</div>;
export const useTheme = () => useContext(ThemeContext);
