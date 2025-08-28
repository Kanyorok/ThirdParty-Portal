// "use client";

// import React, { useState, useEffect, useMemo } from "react";
// import { Button } from "@/components/common/button";
// import { Input } from "@/components/common/input";
// import { Badge } from "@/components/common/badge";
// import { Card, CardContent, CardFooter, CardHeader } from "@/components/common/card";
// import {
//     FileText,
//     PlusCircle,
//     Calendar,
//     Loader2,
//     Info,
//     CheckCircle,
//     Filter,
//     ArrowDownWideNarrow,
//     Clock,
//     Search,
//     X,
//     Building,
//     Users,
//     TrendingUp,
//     AlertCircle
// } from "lucide-react";
// import { motion, AnimatePresence, useReducedMotion, Variants } from "framer-motion";
// import { format, isPast, differenceInDays } from 'date-fns';
// import {
//     Select,
//     SelectContent,
//     SelectItem,
//     SelectTrigger,
//     SelectValue,
// } from "@/components/common/select";
// import { cn } from "@/lib/utils";

// interface PrequalificationRound {
//     id: string;
//     name: string;
//     category: string;
//     applicationDeadline: Date;
//     isApplied: boolean;
//     participantCount?: number;
//     estimatedValue?: string;
//     difficulty?: 'Easy' | 'Medium' | 'Hard';
//     description?: string;
// }

// const mockPrequalificationRounds: PrequalificationRound[] = [
//     {
//         id: "round-001",
//         name: "Annual IT Vendor List Pre-qualification",
//         category: "Information Technology",
//         applicationDeadline: new Date('2025-09-30'),
//         isApplied: false,
//         participantCount: 24,
//         estimatedValue: "$2.5M",
//         difficulty: 'Medium',
//         description: "Comprehensive IT services including software development, maintenance, and support for the upcoming fiscal year."
//     },
//     {
//         id: "round-002",
//         name: "General Construction Suppliers for Infrastructure Projects",
//         category: "Construction",
//         applicationDeadline: new Date('2025-08-15'),
//         isApplied: true,
//         participantCount: 18,
//         estimatedValue: "$5.2M",
//         difficulty: 'Hard',
//         description: "Prequalification for suppliers of materials and services for large-scale public infrastructure projects."
//     },
//     {
//         id: "round-003",
//         name: "Marketing & Creative Services Framework Agreement",
//         category: "Marketing",
//         applicationDeadline: new Date('2025-10-20'),
//         isApplied: false,
//         participantCount: 31,
//         estimatedValue: "$800K",
//         difficulty: 'Easy',
//         description: "Seeking agencies for creative campaigns, digital marketing, and content creation services."
//     },
//     {
//         id: "round-004",
//         name: "Logistics and Transportation Services for Supply Chain",
//         category: "Logistics",
//         applicationDeadline: new Date('2025-07-25'),
//         isApplied: true,
//         participantCount: 15,
//         estimatedValue: "$3.1M",
//         difficulty: 'Medium',
//         description: "Procurement of reliable logistics and transportation partners for nationwide distribution."
//     },
//     {
//         id: "round-005",
//         name: "Strategic Consulting Services Framework",
//         category: "Professional Services",
//         applicationDeadline: new Date('2024-06-01'),
//         isApplied: false,
//         participantCount: 42,
//         estimatedValue: "$1.8M",
//         difficulty: 'Hard',
//         description: "A framework agreement for various strategic consulting needs across business units."
//     },
//     {
//         id: "round-006",
//         name: "Advanced Cybersecurity Solutions & Implementation",
//         category: "Information Technology",
//         applicationDeadline: new Date('2025-08-10'),
//         isApplied: false,
//         participantCount: 8,
//         estimatedValue: "$4.2M",
//         difficulty: 'Hard',
//         description: "Seeking specialized vendors for cutting-edge cybersecurity solutions and their integration."
//     },
//     {
//         id: "round-007",
//         name: "Annual Office Supplies & Equipment Procurement",
//         category: "General Procurement",
//         applicationDeadline: new Date('2025-11-05'),
//         isApplied: false,
//         participantCount: 67,
//         estimatedValue: "$450K",
//         difficulty: 'Easy',
//         description: "Annual tender for the supply of general office consumables, equipment, and furniture."
//     },
// ];

// const containerVariants: Variants = {
//     hidden: { opacity: 0 },
//     visible: {
//         opacity: 1,
//         transition: {
//             staggerChildren: 0.08,
//             delayChildren: 0.1
//         }
//     }
// };

// const itemVariants: Variants = {
//     hidden: { opacity: 0, y: 30, scale: 0.9 },
//     visible: {
//         opacity: 1,
//         y: 0,
//         scale: 1,
//         transition: {
//             type: "spring",
//             stiffness: 120,
//             damping: 18
//         }
//     }
// };

// const headerVariants: Variants = {
//     hidden: { opacity: 0, y: -30 },
//     visible: {
//         opacity: 1,
//         y: 0,
//         transition: {
//             type: "spring",
//             stiffness: 150,
//             damping: 25
//         }
//     }
// };

// export default function PrequalificationRoundsPage() {
//     const [isLoading, setIsLoading] = useState(false);
//     const [rounds, setRounds] = useState<PrequalificationRound[]>([]);
//     const [filterCategory, setFilterCategory] = useState<string>("all");
//     const [sortBy, setSortBy] = useState<string>("deadline-asc");
//     const [searchQuery, setSearchQuery] = useState<string>("");
//     const [selectedDifficulty, setSelectedDifficulty] = useState<string>("all");

//     const shouldReduceMotion = useReducedMotion();

//     useEffect(() => {
//         const fetchData = async () => {
//             setIsLoading(true);
//             try {
//                 await new Promise((resolve) => setTimeout(resolve, 1200));
//                 setRounds(mockPrequalificationRounds);
//             } catch (error) {
//                 console.error("Failed to fetch prequalification rounds:", error);
//             } finally {
//                 setIsLoading(false);
//             }
//         };
//         fetchData();
//     }, []);

//     const filteredAndSortedRounds = useMemo(() => {
//         let currentRounds = [...rounds];

//         if (searchQuery.trim()) {
//             const query = searchQuery.toLowerCase();
//             currentRounds = currentRounds.filter(round =>
//                 round.name.toLowerCase().includes(query) ||
//                 round.category.toLowerCase().includes(query) ||
//                 round.description?.toLowerCase().includes(query)
//             );
//         }

//         if (filterCategory !== "all") {
//             currentRounds = currentRounds.filter(round => round.category === filterCategory);
//         }

//         if (selectedDifficulty !== "all") {
//             currentRounds = currentRounds.filter(round => round.difficulty === selectedDifficulty);
//         }

//         currentRounds.sort((a, b) => {
//             if (sortBy === "deadline-asc") {
//                 return a.applicationDeadline.getTime() - b.applicationDeadline.getTime();
//             } else if (sortBy === "deadline-desc") {
//                 return b.applicationDeadline.getTime() - a.applicationDeadline.getTime();
//             } else if (sortBy === "value-desc") {
//                 const parseValue = (value: string | undefined) => {
//                     if (!value) return 0;
//                     const num = parseFloat(value.replace(/[^0-9.]/g, ''));
//                     if (value.includes('K')) return num * 1000;
//                     if (value.includes('M')) return num * 1000000;
//                     return num;
//                 };
//                 const aValue = parseValue(a.estimatedValue);
//                 const bValue = parseValue(b.estimatedValue);
//                 return bValue - aValue;
//             } else if (sortBy === "participants-asc") {
//                 return (a.participantCount || 0) - (b.participantCount || 0);
//             }
//             return 0;
//         });

//         return currentRounds;
//     }, [rounds, filterCategory, sortBy, searchQuery, selectedDifficulty]);

//     const uniqueCategories = useMemo(() => {
//         const categories = new Set<string>();
//         rounds.forEach(round => categories.add(round.category));
//         return ["all", ...Array.from(categories).sort()];
//     }, [rounds]);

//     const handleApply = (roundId: string) => {
//         setRounds(prevRounds =>
//             prevRounds.map(round =>
//                 round.id === roundId ? { ...round, isApplied: true } : round
//             )
//         );
//     };

//     const handleViewApplication = (roundId: string) => {
//         console.log(`Viewing application for round with ID: ${roundId}`);
//     };

//     const clearAllFilters = () => {
//         setFilterCategory("all");
//         setSortBy("deadline-asc");
//         setSearchQuery("");
//         setSelectedDifficulty("all");
//     };

//     const getDifficultyColor = (difficulty: PrequalificationRound['difficulty']) => {
//         switch (difficulty) {
//             case 'Easy': return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
//             case 'Medium': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
//             case 'Hard': return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
//             default: return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';
//         }
//     };

//     const activeFiltersCount = [
//         filterCategory !== "all",
//         selectedDifficulty !== "all",
//         searchQuery.trim() !== "",
//         sortBy !== "deadline-asc"
//     ].filter(Boolean).length;

//     return (
//         <div className="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50/30 to-indigo-50/40 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 text-gray-900 dark:text-gray-50">
//             <div className="container mx-auto px-4 py-8 md:py-12">
//                 <motion.div
//                     variants={headerVariants}
//                     initial="hidden"
//                     animate="visible"
//                     className="mb-12"
//                 >
//                     <h1 className="text-4xl md:text-5xl font-extrabold bg-gradient-to-r from-blue-700 via-indigo-700 to-purple-700 dark:from-blue-300 dark:via-indigo-300 dark:to-purple-300 bg-clip-text text-transparent mb-4">
//                         Prequalification Rounds
//                     </h1>
//                     <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
//                         <motion.div
//                             initial={{ opacity: 0, y: 30 }}
//                             animate={{ opacity: 1, y: 0 }}
//                             transition={{ delay: 0.2, ...(!shouldReduceMotion && { type: "spring", stiffness: 100, damping: 15 }) }}
//                             className="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-800 flex items-center gap-4 hover:border-blue-300 dark:hover:border-blue-600 transition-colors"
//                         >
//                             <div className="p-3 bg-blue-100 dark:bg-blue-900 rounded-xl flex-shrink-0">
//                                 <FileText className="h-7 w-7 text-blue-600 dark:text-blue-400" />
//                             </div>
//                             <div>
//                                 <p className="text-sm font-medium text-gray-500 dark:text-gray-400">Total Rounds</p>
//                                 <p className="text-3xl font-bold text-gray-900 dark:text-white">{rounds.length}</p>
//                             </div>
//                         </motion.div>

//                         <motion.div
//                             initial={{ opacity: 0, y: 30 }}
//                             animate={{ opacity: 1, y: 0 }}
//                             transition={{ delay: 0.3, ...(!shouldReduceMotion && { type: "spring", stiffness: 100, damping: 15 }) }}
//                             className="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-800 flex items-center gap-4 hover:border-green-300 dark:hover:border-green-600 transition-colors"
//                         >
//                             <div className="p-3 bg-green-100 dark:bg-green-900 rounded-xl flex-shrink-0">
//                                 <CheckCircle className="h-7 w-7 text-green-600 dark:text-green-400" />
//                             </div>
//                             <div>
//                                 <p className="text-sm font-medium text-gray-500 dark:text-gray-400">Applied Rounds</p>
//                                 <p className="text-3xl font-bold text-gray-900 dark:text-white">{rounds.filter(r => r.isApplied).length}</p>
//                             </div>
//                         </motion.div>

//                         <motion.div
//                             initial={{ opacity: 0, y: 30 }}
//                             animate={{ opacity: 1, y: 0 }}
//                             transition={{ delay: 0.4, ...(!shouldReduceMotion && { type: "spring", stiffness: 100, damping: 15 }) }}
//                             className="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-800 flex items-center gap-4 hover:border-orange-300 dark:hover:border-orange-600 transition-colors"
//                         >
//                             <div className="p-3 bg-orange-100 dark:bg-orange-900 rounded-xl flex-shrink-0">
//                                 <Clock className="h-7 w-7 text-orange-600 dark:text-orange-400" />
//                             </div>
//                             <div>
//                                 <p className="text-sm font-medium text-gray-500 dark:text-gray-400">Upcoming Deadlines</p>
//                                 <p className="text-3xl font-bold text-gray-900 dark:text-white">
//                                     {rounds.filter(r => !isPast(r.applicationDeadline) && !r.isApplied).length}
//                                 </p>
//                             </div>
//                         </motion.div>
//                     </div>
//                 </motion.div>

//                 <motion.div
//                     initial={{ opacity: 0, y: 20 }}
//                     animate={{ opacity: 1, y: 0 }}
//                     transition={{ delay: 0.5, ...(!shouldReduceMotion && { type: "spring", stiffness: 100, damping: 15 }) }}
//                     className="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-800 mb-8"
//                 >
//                     <div className="flex flex-col gap-6">
//                         <div className="relative">
//                             <Search className="absolute left-4 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
//                             <Input
//                                 placeholder="Search by name, category, or description..."
//                                 value={searchQuery}
//                                 onChange={(e) => setSearchQuery(e.target.value)}
//                                 className="pl-12 pr-12 py-3 bg-gray-50 dark:bg-slate-700 border-0 focus:ring-2 focus:ring-blue-500 rounded-xl text-base"
//                             />
//                             {searchQuery && (
//                                 <button
//                                     onClick={() => setSearchQuery("")}
//                                     className="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
//                                 >
//                                     <X className="h-5 w-5" />
//                                 </button>
//                             )}
//                         </div>

//                         <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
//                             <Select value={filterCategory} onValueChange={setFilterCategory}>
//                                 <SelectTrigger className="bg-gray-50 dark:bg-slate-700 border-0 rounded-xl py-3 text-base">
//                                     <Filter className="h-5 w-5 mr-3 text-gray-500 dark:text-gray-400" />
//                                     <SelectValue placeholder="Filter by Category" />
//                                 </SelectTrigger>
//                                 <SelectContent className="bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-md">
//                                     {uniqueCategories.map(category => (
//                                         <SelectItem key={category} value={category} className="hover:bg-gray-100 dark:hover:bg-gray-700 text-base">
//                                             {category === "all" ? "All Categories" : category}
//                                         </SelectItem>
//                                     ))}
//                                 </SelectContent>
//                             </Select>

//                             <Select value={selectedDifficulty} onValueChange={setSelectedDifficulty}>
//                                 <SelectTrigger className="bg-gray-50 dark:bg-slate-700 border-0 rounded-xl py-3 text-base">
//                                     <AlertCircle className="h-5 w-5 mr-3 text-gray-500 dark:text-gray-400" />
//                                     <SelectValue placeholder="Filter by Difficulty" />
//                                 </SelectTrigger>
//                                 <SelectContent className="bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-md">
//                                     <SelectItem value="all" className="text-base">All Difficulties</SelectItem>
//                                     <SelectItem value="Easy" className="text-base">Easy</SelectItem>
//                                     <SelectItem value="Medium" className="text-base">Medium</SelectItem>
//                                     <SelectItem value="Hard" className="text-base">Hard</SelectItem>
//                                 </SelectContent>
//                             </Select>

//                             <Select value={sortBy} onValueChange={setSortBy}>
//                                 <SelectTrigger className="bg-gray-50 dark:bg-slate-700 border-0 rounded-xl py-3 text-base">
//                                     <ArrowDownWideNarrow className="h-5 w-5 mr-3 text-gray-500 dark:text-gray-400" />
//                                     <SelectValue placeholder="Sort By" />
//                                 </SelectTrigger>
//                                 <SelectContent className="bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-md">
//                                     <SelectItem value="deadline-asc" className="text-base">Deadline: Soonest First</SelectItem>
//                                     <SelectItem value="deadline-desc" className="text-base">Deadline: Latest First</SelectItem>
//                                     <SelectItem value="value-desc" className="text-base">Value: Highest First</SelectItem>
//                                     <SelectItem value="participants-asc" className="text-base">Participants: Fewest First</SelectItem>
//                                 </SelectContent>
//                             </Select>

//                             <div className="flex items-center justify-end sm:justify-start lg:justify-end gap-2">
//                                 {activeFiltersCount > 0 && (
//                                     <Badge variant="secondary" className="text-sm px-3 py-1.5 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
//                                         {activeFiltersCount} Active Filters
//                                     </Badge>
//                                 )}
//                                 <Button
//                                     onClick={clearAllFilters}
//                                     variant="ghost"
//                                     size="lg"
//                                     className="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-700/50 rounded-xl transition-colors duration-200"
//                                     disabled={activeFiltersCount === 0}
//                                 >
//                                     <X className="h-4 w-4 mr-1" /> Clear All
//                                 </Button>
//                             </div>
//                         </div>
//                     </div>
//                 </motion.div>

//                 <AnimatePresence mode="wait">
//                     {isLoading ? (
//                         <motion.div
//                             key="loading"
//                             initial={{ opacity: 0 }}
//                             animate={{ opacity: 1 }}
//                             exit={{ opacity: 0 }}
//                             className="flex flex-col items-center justify-center py-24 bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-gray-800"
//                         >
//                             <div className="relative">
//                                 <Loader2 className="h-16 w-16 animate-spin text-blue-600" />
//                                 <div className="absolute inset-0 h-16 w-16 border-4 border-blue-200 dark:border-blue-800 rounded-full animate-pulse"></div>
//                             </div>
//                             <p className="text-xl font-medium text-gray-600 dark:text-gray-300 mt-6">
//                                 Fetching exciting opportunities for you...
//                             </p>
//                         </motion.div>
//                     ) : filteredAndSortedRounds.length > 0 ? (
//                         <motion.div
//                             key="rounds-grid"
//                             variants={containerVariants}
//                             initial="hidden"
//                             animate="visible"
//                             className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6"
//                         >
//                             {filteredAndSortedRounds.map((round) => {
//                                 const deadlinePassed = isPast(round.applicationDeadline);
//                                 const daysUntilDeadline = differenceInDays(round.applicationDeadline, new Date());
//                                 const isImminent = !deadlinePassed && daysUntilDeadline <= 7 && daysUntilDeadline >= 0;

//                                 return (
//                                     <motion.div
//                                         key={round.id}
//                                         variants={itemVariants}
//                                         layout
//                                         whileHover={{
//                                             y: shouldReduceMotion ? 0 : -4,
//                                             scale: shouldReduceMotion ? 1 : 1.005,
//                                             borderColor: shouldReduceMotion ? undefined : 'rgb(96 165 250 / var(--tw-border-opacity))', // blue-400
//                                             borderWidth: shouldReduceMotion ? undefined : '2px'
//                                         }}
//                                         transition={{ type: "spring", stiffness: 300, damping: 30 }}
//                                     >
//                                         <Card className="h-full bg-white dark:bg-slate-800 border border-gray-200 dark:border-gray-700 transition-all duration-300 rounded-2xl overflow-hidden relative group">
//                                             {round.isApplied && (
//                                                 <div className="absolute top-0 right-0 bg-gradient-to-r from-green-500 to-emerald-500 text-white text-xs font-bold px-4 py-2 rounded-bl-xl flex items-center gap-1 z-10">
//                                                     <CheckCircle className="h-3 w-3" />
//                                                     Applied
//                                                 </div>
//                                             )}

//                                             <CardHeader className="pb-3">
//                                                 <div className="flex justify-between items-start gap-4">
//                                                     <div className="flex-1">
//                                                         <h3 className="text-xl font-bold text-gray-900 dark:text-white mb-2 line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
//                                                             {round.name}
//                                                         </h3>
//                                                         <div className="flex flex-wrap gap-2 mb-3">
//                                                             <Badge variant="outline" className="text-xs px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 dark:bg-slate-700 dark:text-gray-300 border-transparent">
//                                                                 <Building className="h-3 w-3 mr-1" /> {round.category}
//                                                             </Badge>
//                                                             {round.difficulty && (
//                                                                 <Badge className={cn("text-xs px-2.5 py-1 rounded-full border-transparent", getDifficultyColor(round.difficulty))}>
//                                                                     <AlertCircle className="h-3 w-3 mr-1" /> {round.difficulty}
//                                                                 </Badge>
//                                                             )}
//                                                         </div>
//                                                     </div>
//                                                 </div>

//                                                 {round.description && (
//                                                     <p className="text-sm text-gray-600 dark:text-gray-300 line-clamp-2">
//                                                         {round.description}
//                                                     </p>
//                                                 )}
//                                             </CardHeader>

//                                             <CardContent className="space-y-4">
//                                                 <div className={cn(
//                                                     "flex items-center gap-2 text-sm p-3 rounded-xl",
//                                                     deadlinePassed ? "bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300" :
//                                                         isImminent ? "bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-300" :
//                                                             "bg-gray-50 dark:bg-slate-700 text-gray-700 dark:text-gray-300"
//                                                 )}>
//                                                     {deadlinePassed ? <Info className="h-4 w-4" /> :
//                                                         isImminent ? <Clock className="h-4 w-4 animate-pulse" /> :
//                                                             <Calendar className="h-4 w-4" />}
//                                                     <div className="flex-1">
//                                                         <p className="font-medium">
//                                                             {format(round.applicationDeadline, 'MMM d, yyyy')}
//                                                         </p>
//                                                         <p className="text-xs opacity-75">
//                                                             {deadlinePassed ? "Deadline passed" :
//                                                                 isImminent ? `${daysUntilDeadline} days left!` :
//                                                                     `${daysUntilDeadline} days remaining`}
//                                                         </p>
//                                                     </div>
//                                                 </div>

//                                                 <div className="grid grid-cols-2 gap-3">
//                                                     {round.estimatedValue && (
//                                                         <div className="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-xl flex items-center gap-2">
//                                                             <TrendingUp className="h-4 w-4 text-blue-600 dark:text-blue-400 flex-shrink-0" />
//                                                             <div>
//                                                                 <p className="text-xs text-blue-600 dark:text-blue-400 font-medium mb-0.5">Est. Value</p>
//                                                                 <p className="text-sm font-bold text-blue-700 dark:text-blue-300">{round.estimatedValue}</p>
//                                                             </div>
//                                                         </div>
//                                                     )}
//                                                     {round.participantCount !== undefined && (
//                                                         <div className="bg-purple-50 dark:bg-purple-900/20 p-3 rounded-xl flex items-center gap-2">
//                                                             <Users className="h-4 w-4 text-purple-600 dark:text-purple-400 flex-shrink-0" />
//                                                             <div>
//                                                                 <p className="text-xs text-purple-600 dark:text-purple-400 font-medium mb-0.5">Participants</p>
//                                                                 <p className="text-sm font-bold text-purple-700 dark:text-purple-300">{round.participantCount}</p>
//                                                             </div>
//                                                         </div>
//                                                     )}
//                                                 </div>
//                                             </CardContent>

//                                             <CardFooter className="pt-2">
//                                                 {round.isApplied ? (
//                                                     <Button
//                                                         onClick={() => handleViewApplication(round.id)}
//                                                         variant="secondary"
//                                                         className="w-full rounded-xl font-medium transition-all hover:scale-[1.01] text-base py-3"
//                                                         size="lg"
//                                                     >
//                                                         View Application
//                                                     </Button>
//                                                 ) : (
//                                                     <Button
//                                                         onClick={() => handleApply(round.id)}
//                                                         disabled={deadlinePassed}
//                                                         className={cn(
//                                                             "w-full rounded-xl font-medium transition-all text-base py-3",
//                                                             deadlinePassed ? "opacity-70 cursor-not-allowed bg-gray-200 text-gray-500 dark:bg-gray-700 dark:text-gray-400" : "hover:scale-[1.01] bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white"
//                                                         )}
//                                                         size="lg"
//                                                     >
//                                                         {deadlinePassed ? (
//                                                             <>
//                                                                 <Info className="h-4 w-4 mr-2" />
//                                                                 Deadline Passed
//                                                             </>
//                                                         ) : (
//                                                             <>
//                                                                 <PlusCircle className="h-4 w-4 mr-2" />
//                                                                 Apply Now
//                                                             </>
//                                                         )}
//                                                     </Button>
//                                                 )}
//                                             </CardFooter>
//                                         </Card>
//                                     </motion.div>
//                                 );
//                             })}
//                         </motion.div>
//                     ) : (
//                         <motion.div
//                             key="empty"
//                             initial={{ opacity: 0, scale: 0.95 }}
//                             animate={{ opacity: 1, scale: 1 }}
//                             exit={{ opacity: 0, scale: 0.95 }}
//                             className="text-center py-16 bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-gray-800"
//                         >
//                             <div className="max-w-md mx-auto">
//                                 <div className="w-24 h-24 bg-gray-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-6">
//                                     <FileText className="h-12 w-12 text-gray-400 dark:text-gray-500" />
//                                 </div>
//                                 <h3 className="text-2xl font-bold text-gray-900 dark:text-white mb-2">
//                                     No matching rounds found
//                                 </h3>
//                                 <p className="text-gray-600 dark:text-gray-300 mb-6">
//                                     {activeFiltersCount > 0
//                                         ? "It looks like your current filters are too restrictive. Try adjusting your search criteria or filters."
//                                         : "There are no prequalification opportunities available at the moment. Please check back later!"
//                                     }
//                                 </p>
//                                 {activeFiltersCount > 0 && (
//                                     <Button
//                                         onClick={clearAllFilters}
//                                         variant="outline"
//                                         className="mt-4 px-6 py-3 rounded-xl text-base hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-700 dark:hover:text-blue-300 transition-colors"
//                                     >
//                                         Clear All Filters
//                                     </Button>
//                                 )}
//                             </div>
//                         </motion.div>
//                     )}
//                 </AnimatePresence>
//             </div>
//         </div>
//     );
// }
