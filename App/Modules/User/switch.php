<?php
                            if (isset($_GET['page'])) {
                                $page = $_GET['page'];
                                switch ($page) {
                                    case 'dashboard':
                                        include '../../includes/cards.php';
                                        break;
                                    case 'addEmployee':
                                        include '../../includes/addEmployee.php';
                                        break;
                                    case 'manage':
                                        include '../../includes/manage.php';
                                        break;

                                    case 'records':
                                        include '../../includes/records.php';
                                        break;
                               
                                    case 'history-logs':
                                        include '../../includes/history-logs.php';
                                        break;

                                    case 'searchEmployee':
                                        include '../../includes/search.php';
                                        break;

                                    case 'allEmployees':
                                        include '../../includes/allEmployees.php';
                                        break;
                                    
                                    case 'trainings':
                                        include '../../includes/trainings.php';
                                        break;
                                        
                                    case 'about':
                                        include '../../includes/about.php';
                                        break;

                                    case 'schools':
                                        include '../../includes/schools.php';
                                        break;

                                    case 'positions':
                                        include '../../includes/positions.php';
                                        break;
                                    case 'subjects':
                                        include '../../includes/subjects.php';
                                        break;

                                    case 'backup':
                                        include '../../includes/backup.php';
                                        break;
                                    
                                    case 'research':
                                        include '../../includes/research.php';
                                        break;

                                    case 'account':
                                        include '../../includes/account.php';
                                        break;
                                    case 'create_account':
                                        include '../../includes/create_account.php';
                                        break;

                                    default:
                                        include '../../includes/cards.php';
                                        break;
                                }
                            } else {
                                include '../../includes/cards.php';
                            }

                        ?>