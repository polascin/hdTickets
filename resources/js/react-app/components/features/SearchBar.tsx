import React from 'react';
const SearchBar = ({ onSearch, placeholder, className }: any) => 
  <div className={className}><input placeholder={placeholder} className="w-full p-3 rounded-lg border" /></div>;
export default SearchBar;
